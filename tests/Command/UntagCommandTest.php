<?php

declare(strict_types=1);

namespace PhpTatr\Tests\Command;

use PhpTatr\Command\InitCommand;
use PhpTatr\Command\UntagCommand;
use PhpTatr\Core\Task;
use PhpTatr\Core\TaskRepository;

final class UntagCommandTest extends CommandTestCase
{
    public function testRemovesTagFromMatchingTasks(): void
    {
        $this->runCommand(new InitCommand(), []);
        $repo = new TaskRepository($this->tmpDir . DIRECTORY_SEPARATOR . 'tasks');
        $repo->create(new Task(id: '20260115-143022', title: 'A', tags: ['bug', 'urgent']));
        $repo->create(new Task(id: '20260115-143023', title: 'B', tags: ['feature']));

        [$exitCode, $output] = $this->runCommand(new UntagCommand(), ['-t', 'bug']);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('1 tasks updated', $output);

        $taskA = $repo->findById('20260115-143022');
        $taskB = $repo->findById('20260115-143023');

        $this->assertSame(['urgent'], $taskA->tags);
        $this->assertSame(['feature'], $taskB->tags);
    }

    public function testRequiresAtLeastOneTag(): void
    {
        $this->runCommand(new InitCommand(), []);

        [$exitCode] = $this->runCommand(new UntagCommand(), []);

        $this->assertSame(1, $exitCode);
    }

    public function testSkipsClosedTasksByDefault(): void
    {
        $this->runCommand(new InitCommand(), []);
        $repo = new TaskRepository($this->tmpDir . DIRECTORY_SEPARATOR . 'tasks');
        $repo->create(new Task(id: '20260115-143022', title: 'A', status: 'CLOSED', tags: ['bug']));

        [, $output] = $this->runCommand(new UntagCommand(), ['-t', 'bug']);

        $this->assertStringContainsString('0 tasks updated', $output);
    }
}
