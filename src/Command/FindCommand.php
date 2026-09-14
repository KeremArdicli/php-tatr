<?php

declare(strict_types=1);

namespace PhpTatr\Command;

use PhpTatr\Core\TaskRenderer;
use PhpTatr\Core\TaskRepository;

final class FindCommand
{
    public function run(array $args): int
    {
        $parsed = ArgParser::parse($args);
        $flags = $parsed['flags'];

        if (isset($flags['help'])) {
            echo "Usage: tatr find <HUID> [-path-only]\n";

            return 0;
        }

        if ($parsed['args'] === []) {
            fwrite(STDERR, "Usage: tatr find <HUID> [-path-only]\n");

            return 1;
        }

        $huid = $parsed['args'][0];

        $tasksDir = TaskRepository::findTasksDir(getcwd());

        if ($tasksDir === null) {
            fwrite(STDERR, "Could not find tasks/ folder\n");

            return 1;
        }

        $repo = new TaskRepository($tasksDir);
        $task = $repo->findById($huid);

        if ($task === null) {
            fwrite(STDERR, "No task with HUID `{$huid}` was found\n");

            return 1;
        }

        if (isset($flags['path-only'])) {
            echo $tasksDir . DIRECTORY_SEPARATOR . $task->id . DIRECTORY_SEPARATOR . "TASK.md\n";
        } else {
            echo TaskRenderer::renderLine($task, $repo->getRelativePath()) . "\n";
        }

        return 0;
    }
}
