<?php

declare(strict_types=1);

namespace PhpTatr\Tests\Command;

use PhpTatr\Command\InitCommand;
use PhpTatr\Command\LsCommand;
use PhpTatr\Command\NewCommand;

final class LsCommandTest extends CommandTestCase
{
    public function testNoTasksFound(): void
    {
        $this->runCommand(new InitCommand(), []);
        [$exitCode, $output] = $this->runCommand(new LsCommand(), []);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('No tasks were found', $output);
    }

    public function testListsOpenTasksOnlyByDefault(): void
    {
        $this->runCommand(new InitCommand(), []);
        $this->runCommand(new NewCommand(), ['-t', 'bug', 'Open task']);

        [, $output] = $this->runCommand(new LsCommand(), []);

        $this->assertStringContainsString('Open task', $output);
    }

    public function testFiltersByTagQuery(): void
    {
        $this->runCommand(new InitCommand(), []);
        $this->runCommand(new NewCommand(), ['-t', 'bug', '-s', 'a', 'Bug task']);
        $this->runCommand(new NewCommand(), ['-t', 'feature', '-s', 'b', 'Feature task']);

        [, $output] = $this->runCommand(new LsCommand(), [':bug']);

        $this->assertStringContainsString('Bug task', $output);
        $this->assertStringNotContainsString('Feature task', $output);
    }

    public function testFailsWithoutTasksDir(): void
    {
        [$exitCode] = $this->runCommand(new LsCommand(), []);

        $this->assertSame(1, $exitCode);
    }
}
