<?php

declare(strict_types=1);

namespace PhpTatr\Tests\Command;

use PhpTatr\Command\FindCommand;
use PhpTatr\Command\InitCommand;
use PhpTatr\Core\Task;
use PhpTatr\Core\TaskRepository;

final class FindCommandTest extends CommandTestCase
{
    public function testFindsExistingTask(): void
    {
        $this->runCommand(new InitCommand(), []);
        $repo = new TaskRepository($this->tmpDir . DIRECTORY_SEPARATOR . 'tasks');
        $repo->create(new Task(id: '20260115-143022', title: 'Findable'));

        [$exitCode, $output] = $this->runCommand(new FindCommand(), ['20260115-143022']);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Findable', $output);
    }

    public function testPathOnlyFlag(): void
    {
        $this->runCommand(new InitCommand(), []);
        $repo = new TaskRepository($this->tmpDir . DIRECTORY_SEPARATOR . 'tasks');
        $repo->create(new Task(id: '20260115-143022', title: 'Findable'));

        [, $output] = $this->runCommand(new FindCommand(), ['20260115-143022', '-path-only']);

        $this->assertStringContainsString('20260115-143022' . DIRECTORY_SEPARATOR . 'TASK.md', $output);
    }

    public function testMissingTaskReturnsError(): void
    {
        $this->runCommand(new InitCommand(), []);

        [$exitCode] = $this->runCommand(new FindCommand(), ['20260101-000000']);

        $this->assertSame(1, $exitCode);
    }

    public function testNoArgumentsReturnsError(): void
    {
        $this->runCommand(new InitCommand(), []);

        [$exitCode] = $this->runCommand(new FindCommand(), []);

        $this->assertSame(1, $exitCode);
    }
}
