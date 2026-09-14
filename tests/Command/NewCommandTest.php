<?php

declare(strict_types=1);

namespace PhpTatr\Tests\Command;

use PhpTatr\Command\InitCommand;
use PhpTatr\Command\NewCommand;
use PhpTatr\Core\TaskRepository;

final class NewCommandTest extends CommandTestCase
{
    public function testCreatesTaskWithDefaults(): void
    {
        $this->runCommand(new InitCommand(), []);
        [$exitCode, $output] = $this->runCommand(new NewCommand(), []);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('New Task', $output);

        $repo = new TaskRepository($this->tmpDir . DIRECTORY_SEPARATOR . 'tasks');
        $tasks = $repo->loadAll();

        $this->assertCount(1, $tasks);
        $this->assertSame(100, $tasks[0]->priority);
    }

    public function testCreatesTaskWithTagsAndPriorityAndTitle(): void
    {
        $this->runCommand(new InitCommand(), []);
        $this->runCommand(new NewCommand(), ['-t', 'bug,urgent', '-p', '50', 'Fix', 'the', 'parser']);

        $repo = new TaskRepository($this->tmpDir . DIRECTORY_SEPARATOR . 'tasks');
        $tasks = $repo->loadAll();

        $this->assertCount(1, $tasks);
        $this->assertSame('Fix the parser', $tasks[0]->title);
        $this->assertSame(50, $tasks[0]->priority);
        $this->assertSame(['bug', 'urgent'], $tasks[0]->tags);
    }

    public function testFailsWithoutTasksDir(): void
    {
        [$exitCode] = $this->runCommand(new NewCommand(), []);

        $this->assertSame(1, $exitCode);
    }
}
