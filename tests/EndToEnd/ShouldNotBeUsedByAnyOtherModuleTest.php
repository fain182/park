<?php

declare(strict_types=1);

namespace Park\Tests\EndToEnd;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class ShouldNotBeUsedByAnyOtherModuleTest extends TestCase
{
    private string $binPath;

    protected function setUp(): void
    {
        $this->binPath = __DIR__ . '/../../bin/park';
    }

    public function testShouldNotBeUsedByAnyOtherModuleViolation(): void
    {
        $testDir = __DIR__ . '/fixtures/shouldNotBeUsedByAnyOtherModule/violation';
        
        $process = new Process(['php', $this->binPath, 'validate', 'src'], $testDir);
        $process->run();

        $this->assertEquals(1, $process->getExitCode());
        $this->assertStringContainsString('Architecture violations found', $process->getOutput());
        $this->assertStringContainsString('should not use', $process->getOutput());
        $this->assertStringContainsString('shouldNotBeUsedByAnyOtherModule', $process->getOutput());
    }

    public function testShouldNotBeUsedByAnyOtherModuleSuccess(): void
    {
        $testDir = __DIR__ . '/fixtures/shouldNotBeUsedByAnyOtherModule/success';
        
        $process = new Process(['php', $this->binPath, 'validate', 'src'], $testDir);
        $process->run();

        $this->assertEquals(0, $process->getExitCode());
        $this->assertStringContainsString('All architecture rules are satisfied!', $process->getOutput());
    }
}