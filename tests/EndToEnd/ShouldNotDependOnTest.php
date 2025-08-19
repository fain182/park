<?php

declare(strict_types=1);

namespace Park\Tests\EndToEnd;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ShouldNotDependOnTest extends TestCase
{
    private string $binPath;

    protected function setUp(): void
    {
        $this->binPath = __DIR__ . '/../../bin/park';
    }

    public function testShouldNotDependOnViolation(): void
    {
        $testDir = __DIR__ . '/fixtures/shouldNotDependOn/violation';
        
        $process = new Process(['php', $this->binPath, 'validate', 'src'], $testDir);
        $process->run();

        $this->assertEquals(1, $process->getExitCode());
        $this->assertStringContainsString('Architecture violations found', $process->getOutput());
        $this->assertStringContainsString('should not depend on', $process->getOutput());
        $this->assertStringContainsString('shouldNotDependOn', $process->getOutput());
    }

    public function testShouldNotDependOnSuccess(): void
    {
        $testDir = __DIR__ . '/fixtures/shouldNotDependOn/success';
        
        $process = new Process(['php', $this->binPath, 'validate', 'src'], $testDir);
        $process->run();

        $this->assertEquals(0, $process->getExitCode());
        $this->assertStringContainsString('All architecture rules are satisfied!', $process->getOutput());
    }
}