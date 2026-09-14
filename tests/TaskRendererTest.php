<?php

declare(strict_types=1);

namespace PhpTatr\Tests;

use PhpTatr\Core\Task;
use PhpTatr\Core\TaskRenderer;
use PHPUnit\Framework\TestCase;

final class TaskRendererTest extends TestCase
{
    public function testRenderNewMinimal(): void
    {
        $task = new Task(
            id: '20260115-143022',
            title: 'Fix bug',
            status: 'OPEN',
            priority: 100,
            tags: ['bug', 'urgent'],
        );

        $expected = "# Fix bug\n\n- STATUS: OPEN\n- PRIORITY: 100\n- TAGS: bug,urgent\n\nNo description.\n";

        $this->assertSame($expected, TaskRenderer::renderNew($task));
    }

    public function testRenderPreservesBody(): void
    {
        $task = new Task(
            id: 'id',
            title: 'Title',
            status: 'OPEN',
            priority: 100,
            tags: [],
            properties: ['STATUS' => 'OPEN'],
            body: "\nSome body text\nmore lines\n",
        );

        $rendered = TaskRenderer::render($task);

        $this->assertStringContainsString("Some body text\nmore lines\n", $rendered);
    }

    public function testRenderPreservesPropertyOrder(): void
    {
        $task = new Task(
            id: 'id',
            title: 'Title',
            status: 'OPEN',
            priority: 100,
            tags: [],
            properties: ['OWNER' => 'kerem', 'STATUS' => 'OPEN'],
            body: '',
        );

        $rendered = TaskRenderer::render($task);
        $ownerPos = strpos($rendered, '- OWNER: kerem');
        $statusPos = strpos($rendered, '- STATUS: OPEN');

        $this->assertNotFalse($ownerPos);
        $this->assertNotFalse($statusPos);
        $this->assertLessThan($statusPos, $ownerPos);
    }

    public function testRenderLineFormat(): void
    {
        $task = new Task(
            id: '20260115-143022',
            title: 'Fix bug',
            status: 'OPEN',
            priority: 5,
            tags: [],
        );

        $line = TaskRenderer::renderLine($task, 'tasks');

        $this->assertSame('tasks/20260115-143022/TASK.md:1: OPEN [PRIORITY:   5] Fix bug', $line);
    }

    public function testRenderLineWithTags(): void
    {
        $task = new Task(
            id: 'id',
            title: 'Title',
            status: 'OPEN',
            priority: 100,
            tags: ['bug', 'ui'],
        );

        $line = TaskRenderer::renderLine($task);

        $this->assertStringContainsString('[bug,ui]', $line);
    }

    public function testRenderLineWithoutTags(): void
    {
        $task = new Task(id: 'id', title: 'Title', tags: []);

        $line = TaskRenderer::renderLine($task);

        $this->assertStringNotContainsString('[]', $line);
    }

    public function testRenderLinePriorityPadding(): void
    {
        $task = new Task(id: 'id', title: 'Title', priority: 7);

        $line = TaskRenderer::renderLine($task);

        $this->assertStringContainsString('[PRIORITY:   7]', $line);
    }
}
