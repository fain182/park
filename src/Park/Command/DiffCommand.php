<?php

declare(strict_types=1);

namespace Park\Command;

use Park\Domain\ArchitectureAnalysis;
use Park\Domain\DependencyGraph;
use Park\Domain\Violation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

class DiffCommand extends Command
{
    protected static $defaultName = 'diff';
    
    protected function configure(): void
    {
        $this->setName('diff')
             ->setDescription('Compare violations between current branch and base branch')
             ->addArgument('directory', InputArgument::OPTIONAL, 'Directory to analyze', 'src')
             ->addOption('base', 'b', InputOption::VALUE_REQUIRED, 'Base branch to compare against', 'main');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $directory = $input->getArgument('directory');
        $baseBranch = $input->getOption('base');
        
        if (!is_dir($directory)) {
            $io->error("Directory '{$directory}' not found");
            return Command::FAILURE;
        }
        
        $configFile = 'park.config.php';
        if (!file_exists($configFile)) {
            $io->error("Configuration file '{$configFile}' not found");
            return Command::FAILURE;
        }

        // Analyze current state
        $currentViolations = $this->analyzeViolations($directory, $configFile);
        
        // Analyze base branch
        $baseViolations = $this->analyzeBaseBranch($directory, $configFile, $baseBranch);
        if ($baseViolations === null) {
            $io->error("Failed to analyze base branch '{$baseBranch}'");
            return Command::FAILURE;
        }
        
        // Compare violations
        $diff = $this->compareViolations($currentViolations, $baseViolations);
        
        // Output results
        $this->outputResults($io, $diff, $baseBranch);
        
        // Return appropriate exit code
        return empty($diff['added']) ? Command::SUCCESS : Command::FAILURE;
    }
    
    /** @return Violation[] */
    private function analyzeViolations(string $directory, string $configFile): array
    {
        $rules = require $configFile;
        $graph = DependencyGraph::fromDirectory($directory);
        $analysis = new ArchitectureAnalysis($rules, $graph);
        
        return $analysis->getViolations()->getViolations();
    }
    
    /** @return Violation[]|null */
    private function analyzeBaseBranch(string $directory, string $configFile, string $baseBranch): ?array
    {
        // Check if we're in a git repository
        $process = new Process(['git', 'rev-parse', '--git-dir']);
        $process->run();
        if (!$process->isSuccessful()) {
            return null;
        }
        
        // Check if base branch exists
        $process = new Process(['git', 'rev-parse', '--verify', $baseBranch]);
        $process->run();
        if (!$process->isSuccessful()) {
            return null;
        }
        
        // Stash current changes if any
        $hasChanges = false;
        $process = new Process(['git', 'diff-index', '--quiet', 'HEAD', '--']);
        $process->run();
        if (!$process->isSuccessful()) {
            $hasChanges = true;
            $process = new Process(['git', 'stash', 'push', '-m', 'park-diff-temp']);
            $process->run();
        }
        
        // Get current branch
        $process = new Process(['git', 'rev-parse', '--abbrev-ref', 'HEAD']);
        $process->run();
        $currentBranch = trim($process->getOutput());
        
        try {
            // Checkout base branch
            $process = new Process(['git', 'checkout', $baseBranch]);
            $process->run();
            if (!$process->isSuccessful()) {
                return null;
            }
            
            // Analyze violations in base branch
            $violations = $this->analyzeViolations($directory, $configFile);
            
            return $violations;
            
        } finally {
            // Always restore original state
            $process = new Process(['git', 'checkout', $currentBranch]);
            $process->run();
            
            if ($hasChanges) {
                $process = new Process(['git', 'stash', 'pop']);
                $process->run();
            }
        }
    }
    
    private function compareViolations(array $current, array $base): array
    {
        $currentStrings = array_map(fn(Violation $v) => (string) $v, $current);
        $baseStrings = array_map(fn(Violation $v) => (string) $v, $base);
        
        $added = array_diff($currentStrings, $baseStrings);
        $removed = array_diff($baseStrings, $currentStrings);
        
        return [
            'added' => array_values($added),
            'removed' => array_values($removed)
        ];
    }
    
    private function outputResults(SymfonyStyle $io, array $diff, string $baseBranch): void
    {
        $io->title("📊 Violation Diff vs {$baseBranch} branch");
        
        if (!empty($diff['added'])) {
            $io->section("➕ New violations (" . count($diff['added']) . "):");
            foreach ($diff['added'] as $violation) {
                $io->writeln("  {$violation}");
            }
        }
        
        if (!empty($diff['removed'])) {
            $io->section("➖ Fixed violations (" . count($diff['removed']) . "):");
            foreach ($diff['removed'] as $violation) {
                $io->writeln("  {$violation}");
            }
        }
        
        if (empty($diff['added']) && empty($diff['removed'])) {
            $io->success("No changes in violations compared to {$baseBranch}");
            return;
        }
        
        if (!empty($diff['added'])) {
            $io->error("⚠️  CI Status: FAILED (new violations detected)");
        } else {
            $io->success("✅ CI Status: PASSED (no new violations)");
        }
    }
}