<?php

declare(strict_types=1);

namespace PhpTatr\Command;

use PhpTatr\Core\Huid;
use PhpTatr\Core\TaskRenderer;
use PhpTatr\Core\TaskRepository;

final class RefCommand
{
    public function run(array $args): int
    {
        $parsed = ArgParser::parse($args);
        $flags = $parsed['flags'];

        if (isset($flags['help'])) {
            echo "Usage: tatr ref [HUID]\n";

            return 0;
        }

        $huid = $parsed['args'][0] ?? basename(getcwd());

        if (!Huid::isValid($huid)) {
            fwrite(STDERR, "Not a valid HUID: {$huid}\n");

            return 1;
        }

        $tasksDir = TaskRepository::findTasksDir(getcwd());

        if ($tasksDir === null) {
            fwrite(STDERR, "Could not find tasks/ folder\n");

            return 1;
        }

        $repo = new TaskRepository($tasksDir);
        $relPath = $repo->getRelativePath();
        $found = false;

        foreach ($repo->loadAll() as $task) {
            if ($task->id === $huid) {
                continue;
            }

            if (str_contains($task->rawContent, $huid)) {
                $found = true;
                echo TaskRenderer::renderLine($task, $relPath) . "\n";
            }
        }

        if (!$found) {
            echo "No references to `{$huid}` were found\n";
        }

        return 0;
    }
}
