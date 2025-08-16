<?php

declare(strict_types=1);

namespace Park\Tests\EndToEnd;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ExceptionsTest extends TestCase
{
    private string $binPath;

    protected function setUp(): void
    {
        $this->binPath = __DIR__ . '/../../bin/park';
    }

    public function testExceptionsAreRespected(): void
    {
        $testDir = __DIR__ . '/fixtures/exceptionsTest';
        
        $process = new Process(['php', $this->binPath, 'src'], $testDir);
        $process->run();

        // Should find violation for User but not for Legacy\OldUser
        $this->assertEquals(1, $process->getExitCode());
        $this->assertStringContainsString('Architecture violations found', $process->getOutput());
        $this->assertStringContainsString('App\Domain', $process->getOutput());
        $this->assertStringNotContainsString('Legacy', $process->getOutput());
    }
}