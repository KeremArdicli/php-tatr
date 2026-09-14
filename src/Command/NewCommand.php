<?php

declare(strict_types=1);

namespace PhpTatr\Command;

use PhpTatr\Core\Huid;
use PhpTatr\Core\Task;
use PhpTatr\Core\TaskParser;
use PhpTatr\Core\TaskRenderer;
use PhpTatr\Core\TaskRepository;

final class NewCommand
{
    private const DEFAULT_PRIORITY = 100;

    public function run(array $args): int
    {
        $parsed = ArgParser::parse($args, flagsWithValues: ['t', 'p', 's']);
        $flags = $parsed['flags'];

        if (isset($flags['help'])) {
            echo "Usage: tatr new [-t tags] [-p priority] [-s suffix] [TITLE...]\n";

            return 0;
        }

        $tasksDir = TaskRepository::findTasksDir(getcwd());

        if ($tasksDir === null) {
            fwrite(STDERR, "Could not find tasks/ folder\n");

            return 1;
        }

        $title = $parsed['args'] !== [] ? implode(' ', $parsed['args']) : 'New Task';
        $tags = isset($flags['t']) ? TaskParser::parseTags((string) $flags['t']) : [];
        $priority = isset($flags['p']) ? (int) $flags['p'] : self::DEFAULT_PRIORITY;
        $suffix = isset($flags['s']) ? (string) $flags['s'] : null;

        $id = Huid::generate($suffix);

        $task = new Task(
            id: $id,
            title: $title,
            status: 'OPEN',
            priority: $priority,
            tags: $tags,
        );

        $repo = new TaskRepository($tasksDir);
        $repo->create($task);

        echo TaskRenderer::renderLine($task, $repo->getRelativePath()) . "\n";

        return 0;
    }
}
