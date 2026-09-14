<?php

declare(strict_types=1);

namespace PhpTatr\Tests\Command;

use PhpTatr\Command\GraphCommand;
use PhpTatr\Command\InitCommand;
use PhpTatr\Core\Task;
use PhpTatr\Core\TaskRepository;

final class GraphCommandTest extends CommandTestCase
{
    public function testGeneratesDotFileWithEdges(): void
    {
        $this->runCommand(new InitCommand(), []);
        $repo = new TaskRepository($this->tmpDir . DIRECTORY_SEPARATOR . 'tasks');
        $repo->create(new Task(id: '20260115-143022', title: 'Target'));
        $repo->create(new Task(id: '20260115-143023', title: 'Referrer'));

        $taskMdPath = $this->tmpDir . DIRECTORY_SEPARATOR . 'tasks' . DIRECTORY_SEPARATOR
            . '20260115-143023' . DIRECTORY_SEPARATOR . 'TASK.md';
        file_put_contents($taskMdPath, file_get_contents($taskMdPath) . "\nSee 20260115-143022\n");

        [$exitCode, $output] = $this->runCommand(new GraphCommand(), []);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('graph.dot', $output);

        $dot = file_get_contents($this->tmpDir . DIRECTORY_SEPARATOR . 'graph.dot');

        $this->assertStringContainsString('digraph tasks', $dot);
        $this->assertStringContainsString('"20260115-143023" -> "20260115-143022"', $dot);
        $this->assertStringContainsString('label="Target"', $dot);
    }

    public function testFailsWithoutTasksDir(): void
    {
        [$exitCode] = $this->runCommand(new GraphCommand(), []);

        $this->assertSame(1, $exitCode);
    }
}
