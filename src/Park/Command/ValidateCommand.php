<?php

declare(strict_types=1);

namespace Park\Command;

use Park\Validator\RuleValidator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ValidateCommand extends Command
{
    protected static $defaultName = null;
    
    protected function configure(): void
    {
        $this->setName('validate')
             ->addArgument('directory', InputArgument::OPTIONAL, 'Directory to analyze', 'src');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $directory = $input->getArgument('directory');
        
        if (!is_dir($directory)) {
            $io->error("Directory '{$directory}' not found");
            return Command::FAILURE;
        }
        
        $configFile = 'park.config.php';
        if (!file_exists($configFile)) {
            $io->error("Configuration file '{$configFile}' not found");
            return Command::FAILURE;
        }

        $rules = require $configFile;
        
        $validator = new RuleValidator();
        $violations = $validator->validate($rules, $directory);
        
        if (empty($violations)) {
            $io->success('All architecture rules are satisfied!');
            return Command::SUCCESS;
        }
        
        $io->error('Architecture violations found:');
        foreach ($violations as $violation) {
            $io->text("- {$violation}");
        }
        
        return Command::FAILURE;
    }
}