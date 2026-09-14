<?php

declare(strict_types=1);

namespace PhpTatr\Tests;

use PhpTatr\Core\TaskParser;
use PHPUnit\Framework\TestCase;

final class TaskParserTest extends TestCase
{
    public function testParseMinimalTask(): void
    {
        $task = TaskParser::parse('20260115-143022', "# Title\n");

        $this->assertSame('Title', $task->title);
        $this->assertSame('OPEN', $task->status);
        $this->assertSame(999999, $task->priority);
        $this->assertSame([], $task->tags);
    }

    public function testParseFullTask(): void
    {
        $content = <<<MD
        # completing-read in tasks-create-from-title does not allow spaces

        - STATUS: CLOSED
        - PRIORITY: 100
        - TAGS: emacs

        This task describes a bug in the emacs integration.
        Multiple lines of description are supported.
        MD;

        $task = TaskParser::parse('20260115-143022', $content);

        $this->assertSame('completing-read in tasks-create-from-title does not allow spaces', $task->title);
        $this->assertSame('CLOSED', $task->status);
        $this->assertSame(100, $task->priority);
        $this->assertSame(['emacs'], $task->tags);
        $this->assertStringContainsString('This task describes a bug', $task->body);
    }

    public function testParseTaskWithCustomProperties(): void
    {
        $content = "# Title\n\n- STATUS: OPEN\n- OWNER: kerem\n\nBody\n";

        $task = TaskParser::parse('id', $content);

        $this->assertSame('kerem', $task->properties['OWNER']);
    }

    public function testParseTags(): void
    {
        $this->assertSame(['bug', 'urgent', 'fix'], TaskParser::parseTags('bug,urgent fix'));
    }

    public function testParseEmptyTags(): void
    {
        $this->assertSame([], TaskParser::parseTags(''));
    }

    public function testParseTaskWithNoTitle(): void
    {
        $task = TaskParser::parse('id', "no title here\n");

        $this->assertSame('!!! INVALID: TASK TITLE MUST START WITH # !!!', $task->title);
    }

    public function testPropertyOrder(): void
    {
        $content = "# Title\n\n- OWNER: kerem\n- STATUS: OPEN\n\nBody\n";

        $task = TaskParser::parse('id', $content);

        $this->assertSame(['OWNER', 'STATUS'], array_keys($task->properties));
    }

    public function testParseDefaultStatus(): void
    {
        $task = TaskParser::parse('id', "# Title\n\nBody\n");

        $this->assertSame('OPEN', $task->status);
    }

    public function testParseDefaultPriority(): void
    {
        $task = TaskParser::parse('id', "# Title\n\nBody\n");

        $this->assertSame(999999, $task->priority);
    }
}
