<?php

declare(strict_types=1);

namespace PhpTatr\Tests\Command;

use PhpTatr\Command\InitCommand;
use PhpTatr\Command\SummaryCommand;
use PhpTatr\Core\Task;
use PhpTatr\Core\TaskRepository;

final class SummaryCommandTest extends CommandTestCase
{
    public function testSummaryCountsOpenTasksByTag(): void
    {
        $this->runCommand(new InitCommand(), []);
        $repo = new TaskRepository($this->tmpDir . DIRECTORY_SEPARATOR . 'tasks');
        $repo->create(new Task(id: '20260115-143022', title: 'A', tags: ['bug']));
        $repo->create(new Task(id: '20260115-143023', title: 'B', tags: ['bug']));
        $repo->create(new Task(id: '20260115-143024', title: 'C'));

        [$exitCode, $output] = $this->runCommand(new SummaryCommand(), []);

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('STATUS:   OPEN', $output);
        $this->assertStringContainsString('TOTAL:    3', $output);
        $this->assertStringContainsString('UNTAGGED: 1', $output);
        $this->assertStringContainsString('bug', $output);
    }

    public function testClosedFlagSwitchesStatus(): void
    {
        $this->runCommand(new InitCommand(), []);
        $repo = new TaskRepository($this->tmpDir . DIRECTORY_SEPARATOR . 'tasks');
        $repo->create(new Task(id: '20260115-143022', title: 'Closed one', status: 'CLOSED'));

        [, $output] = $this->runCommand(new SummaryCommand(), ['-c']);

        $this->assertStringContainsString('STATUS:   CLOSED', $output);
        $this->assertStringContainsString('TOTAL:    1', $output);
    }
}
