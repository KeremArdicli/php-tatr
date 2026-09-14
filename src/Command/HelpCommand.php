<?php

declare(strict_types=1);

namespace PhpTatr\Command;

final class HelpCommand
{
    private const COMMANDS = [
        'init' => 'Create a tasks/ directory in the current folder',
        'new' => 'Create a new task',
        'ls' => 'List tasks matching a TQL query',
        'find' => 'Find a task by its HUID',
        'summary' => 'Show task counts grouped by tag',
        'ref' => 'Find tasks that reference a given HUID',
        'untag' => 'Remove tags from tasks matching a TQL query',
        'graph' => 'Generate a dependency graph of tasks',
        'version' => 'Show the tatr version',
        'help' => 'Show this help message',
    ];

    public function run(array $args): int
    {
        $maxWidth = max(array_map('strlen', array_keys(self::COMMANDS)));

        echo "Usage: tatr <command> [options]\n\n";
        echo "Commands:\n";

        foreach (self::COMMANDS as $name => $description) {
            echo '  ' . str_pad($name, $maxWidth) . " - {$description}\n";
        }

        return 0;
    }
}
