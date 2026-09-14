<?php

declare(strict_types=1);

namespace PhpTatr\Command;

use PhpTatr\Core\TaskRepository;

final class SummaryCommand
{
    public function run(array $args): int
    {
        $parsed = ArgParser::parse($args);
        $flags = $parsed['flags'];

        if (isset($flags['help'])) {
            echo "Usage: tatr summary [-c]\n";

            return 0;
        }

        $tasksDir = TaskRepository::findTasksDir(getcwd());

        if ($tasksDir === null) {
            fwrite(STDERR, "Could not find tasks/ folder\n");

            return 1;
        }

        $repo = new TaskRepository($tasksDir);
        $showClosed = isset($flags['c']);
        $status = $showClosed ? 'CLOSED' : 'OPEN';

        $tasks = array_values(array_filter(
            $repo->loadAll(),
            fn ($task) => strtoupper($task->status) === $status,
        ));

        $untagged = 0;
        $tagCounts = [];

        foreach ($tasks as $task) {
            if ($task->tags === []) {
                $untagged++;
                continue;
            }

            foreach ($task->tags as $tag) {
                $tagCounts[$tag] = ($tagCounts[$tag] ?? 0) + 1;
            }
        }

        arsort($tagCounts);

        echo "STATUS:   {$status}\n";
        echo 'TOTAL:    ' . count($tasks) . "\n";

        if ($untagged > 0) {
            echo "UNTAGGED: {$untagged}\n";
        }

        if ($tagCounts !== []) {
            $registry = $repo->loadTagRegistry();
            $maxWidth = max(array_map('strlen', array_keys($tagCounts)));

            echo "TAGGED:\n";

            foreach ($tagCounts as $tag => $count) {
                $line = '    ' . str_pad($tag, $maxWidth) . sprintf(' => %4d', $count);
                $description = $registry->getDescription($tag);

                if ($description !== null && $description !== '') {
                    $line .= " - {$description}";
                }

                echo $line . "\n";
            }
        }

        return 0;
    }
}
