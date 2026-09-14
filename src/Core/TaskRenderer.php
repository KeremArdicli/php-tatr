<?php

declare(strict_types=1);

namespace PhpTatr\Core;

final class TaskRenderer
{
    public static function renderNew(Task $task): string
    {
        $content = "# {$task->title}\n\n";
        $content .= "- STATUS: {$task->status}\n";
        $content .= "- PRIORITY: {$task->priority}\n";
        $content .= '- TAGS: ' . implode(',', $task->tags) . "\n";
        $content .= "\nNo description.\n";

        return $content;
    }

    public static function render(Task $task): string
    {
        $content = "# {$task->title}\n\n";

        foreach ($task->properties as $key => $value) {
            $content .= match ($key) {
                'TAGS' => '- TAGS: ' . implode(',', $task->tags) . "\n",
                'STATUS' => "- STATUS: {$task->status}\n",
                'PRIORITY' => "- PRIORITY: {$task->priority}\n",
                default => "- {$key}: {$value}\n",
            };
        }

        $content .= $task->body;

        return $content;
    }

    public static function renderLine(Task $task, string $relPath = 'tasks'): string
    {
        $line = "{$relPath}/{$task->id}/TASK.md:1: {$task->status}";
        $line .= sprintf(' [PRIORITY: %3d]', $task->priority);

        if ($task->tags !== []) {
            $line .= ' [' . implode(',', $task->tags) . ']';
        }

        $line .= " {$task->title}";

        return $line;
    }
}
