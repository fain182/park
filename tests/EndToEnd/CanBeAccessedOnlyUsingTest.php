<?php

declare(strict_types=1);

namespace Park\Tests\EndToEnd;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class CanBeAccessedOnlyUsingTest extends TestCase
{
    private string $binPath;

    protected function setUp(): void
    {
        $this->binPath = __DIR__ . '/../../bin/park';
    }

    public function testCanBeAccessedOnlyUsingViolation(): void
    {
        $testDir = __DIR__ . '/fixtures/canBeAccessedOnlyUsing/violation';
        
        $process = new Process(['php', $this->binPath, 'validate', 'src'], $testDir);
        $process->run();

        $this->assertEquals(1, $process->getExitCode());
        $this->assertStringContainsString('Architecture violations found', $process->getOutput());
        $this->assertStringContainsString('cannot access private class', $process->getOutput());
        $this->assertStringContainsString('canBeAccessedOnlyUsing', $process->getOutput());
    }

    public function testCanBeAccessedOnlyUsingSuccess(): void
    {
        $testDir = __DIR__ . '/fixtures/canBeAccessedOnlyUsing/success';
        
        $process = new Process(['php', $this->binPath, 'validate', 'src'], $testDir);
        $process->run();

        $this->assertEquals(0, $process->getExitCode());
        $this->assertStringContainsString('All architecture rules are satisfied!', $process->getOutput());
    }
}