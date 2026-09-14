<?php

declare(strict_types=1);

namespace PhpTatr\Command;

use PhpTatr\Core\TaskRepository;

final class InitCommand
{
    public function run(array $args): int
    {
        $parsed = ArgParser::parse($args);
        $flags = $parsed['flags'];

        if (isset($flags['help'])) {
            echo "Usage: tatr init [-no-readme] [-help]\n";

            return 0;
        }

        $cwd = getcwd();
        $tasksDir = $cwd . DIRECTORY_SEPARATOR . 'tasks';

        if (is_dir($tasksDir)) {
            fwrite(STDERR, "tasks/ directory already exists\n");

            return 1;
        }

        TaskRepository::init($cwd, withReadme: !isset($flags['no-readme']));

        echo "Created {$tasksDir}\n";

        return 0;
    }
}
