<?php

declare(strict_types=1);

namespace PhpTatr\Tests\Command;

use PhpTatr\Command\InitCommand;

final class InitCommandTest extends CommandTestCase
{
    public function testCreatesTasksDirectory(): void
    {
        [$exitCode, $output] = $this->runCommand(new InitCommand(), []);

        $this->assertSame(0, $exitCode);
        $this->assertDirectoryExists($this->tmpDir . DIRECTORY_SEPARATOR . 'tasks');
        $this->assertFileExists($this->tmpDir . DIRECTORY_SEPARATOR . 'tasks' . DIRECTORY_SEPARATOR . 'README.md');
        $this->assertStringContainsString('Created', $output);
    }

    public function testNoReadmeFlagSkipsReadme(): void
    {
        $this->runCommand(new InitCommand(), ['-no-readme']);

        $this->assertFileDoesNotExist($this->tmpDir . DIRECTORY_SEPARATOR . 'tasks' . DIRECTORY_SEPARATOR . 'README.md');
    }

    public function testFailsWhenTasksDirAlreadyExists(): void
    {
        $this->runCommand(new InitCommand(), []);
        [$exitCode] = $this->runCommand(new InitCommand(), []);

        $this->assertSame(1, $exitCode);
    }

    public function testHelpFlag(): void
    {
        [$exitCode, $output] = $this->runCommand(new InitCommand(), ['-help']);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Usage', $output);
    }
}
