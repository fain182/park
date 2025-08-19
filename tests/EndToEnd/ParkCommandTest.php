<?php

declare(strict_types=1);

namespace Park\Tests\EndToEnd;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ParkCommandTest extends TestCase
{
    private string $binPath;

    protected function setUp(): void
    {
        $this->binPath = __DIR__ . '/../../bin/park';
    }

    public function testAllRulesSatisfied(): void
    {
        $testDir = __DIR__ . '/fixtures/allRulesSatisfied';
        
        $process = new Process(['php', $this->binPath, 'validate', 'src'], $testDir);
        $process->run();

        $this->assertEquals(0, $process->getExitCode());
        $this->assertStringContainsString('All architecture rules are satisfied!', $process->getOutput());
    }

    public function testMissingConfigurationFile(): void
    {
        $testDir = __DIR__ . '/fixtures/missingConfig';
        
        $process = new Process(['php', $this->binPath, 'validate', 'src'], $testDir);
        $process->run();

        $this->assertEquals(1, $process->getExitCode());
        $this->assertStringContainsString('Configuration file \'park.config.php\' not found', $process->getOutput());
    }

    public function testMissingDirectory(): void
    {
        $process = new Process(['php', $this->binPath, 'validate', 'nonexistent']);
        $process->run();

        $this->assertEquals(1, $process->getExitCode());
        $this->assertStringContainsString('Directory \'nonexistent\' not found', $process->getOutput());
    }
}