<?php

declare(strict_types=1);

namespace PhpTatr\Tests\Command;

use PhpTatr\Command\InitCommand;
use PhpTatr\Command\RefCommand;
use PhpTatr\Core\Task;
use PhpTatr\Core\TaskRepository;

final class RefCommandTest extends CommandTestCase
{
    public function testFindsReferencingTask(): void
    {
        $this->runCommand(new InitCommand(), []);
        $repo = new TaskRepository($this->tmpDir . DIRECTORY_SEPARATOR . 'tasks');
        $repo->create(new Task(id: '20260115-143022', title: 'Target'));
        $referrer = new Task(id: '20260115-143023', title: 'Referrer');
        $repo->create($referrer);

        $taskMdPath = $this->tmpDir . DIRECTORY_SEPARATOR . 'tasks' . DIRECTORY_SEPARATOR
            . '20260115-143023' . DIRECTORY_SEPARATOR . 'TASK.md';
        file_put_contents($taskMdPath, file_get_contents($taskMdPath) . "\nSee 20260115-143022\n");

        [$exitCode, $output] = $this->runCommand(new RefCommand(), ['20260115-143022']);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Referrer', $output);
    }

    public function testNoReferencesFound(): void
    {
        $this->runCommand(new InitCommand(), []);
        $repo = new TaskRepository($this->tmpDir . DIRECTORY_SEPARATOR . 'tasks');
        $repo->create(new Task(id: '20260115-143022', title: 'Lonely'));

        [$exitCode, $output] = $this->runCommand(new RefCommand(), ['20260115-143022']);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('No references to', $output);
    }

    public function testInvalidHuidReturnsError(): void
    {
        $this->runCommand(new InitCommand(), []);

        [$exitCode] = $this->runCommand(new RefCommand(), ['not-a-huid']);

        $this->assertSame(1, $exitCode);
    }
}
