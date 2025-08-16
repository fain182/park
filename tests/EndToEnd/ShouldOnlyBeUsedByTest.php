<?php

declare(strict_types=1);

namespace Park\Tests\EndToEnd;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ShouldOnlyBeUsedByTest extends TestCase
{
    private string $binPath;

    protected function setUp(): void
    {
        $this->binPath = __DIR__ . '/../../bin/park';
    }

    public function testShouldOnlyBeUsedByViolation(): void
    {
        $testDir = __DIR__ . '/fixtures/shouldOnlyBeUsedBy/violation';
        
        $process = new Process(['php', $this->binPath, 'src'], $testDir);
        $process->run();

        $this->assertEquals(1, $process->getExitCode());
        $this->assertStringContainsString('Architecture violations found', $process->getOutput());
        $this->assertStringContainsString('is not allowed to use', $process->getOutput());
        $this->assertStringContainsString('shouldOnlyBeUsedBy', $process->getOutput());
    }

    public function testShouldOnlyBeUsedBySuccess(): void
    {
        $testDir = __DIR__ . '/fixtures/shouldOnlyBeUsedBy/success';
        
        $process = new Process(['php', $this->binPath, 'src'], $testDir);
        $process->run();

        $this->assertEquals(0, $process->getExitCode());
        $this->assertStringContainsString('All architecture rules are satisfied!', $process->getOutput());
    }
}