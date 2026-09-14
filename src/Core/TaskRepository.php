<?php

declare(strict_types=1);

namespace PhpTatr\Core;

final class TaskRepository
{
    public function __construct(
        private readonly string $tasksDir,
    ) {
    }

    public static function findTasksDir(string $startDir): ?string
    {
        $dir = realpath($startDir);

        if ($dir === false) {
            return null;
        }

        while (true) {
            $candidate = $dir . DIRECTORY_SEPARATOR . 'tasks';

            if (is_dir($candidate)) {
                return $candidate;
            }

            $parent = dirname($dir);

            if ($parent === $dir) {
                return null;
            }

            $dir = $parent;
        }
    }

    public static function init(string $parentDir, bool $withReadme = true): string
    {
        $tasksDir = $parentDir . DIRECTORY_SEPARATOR . 'tasks';

        mkdir($tasksDir);

        if ($withReadme) {
            file_put_contents(
                $tasksDir . DIRECTORY_SEPARATOR . 'README.md',
                "# Tasks\n\nThis directory is managed by tatr.\n",
            );
        }

        return $tasksDir;
    }

    /**
     * @return list<Task>
     */
    public function loadAll(): array
    {
        $tasks = [];

        foreach (scandir($this->tasksDir) as $entry) {
            if ($entry === '.' || $entry === '..' || !Huid::isValid($entry)) {
                continue;
            }

            $taskMdPath = $this->tasksDir . DIRECTORY_SEPARATOR . $entry . DIRECTORY_SEPARATOR . 'TASK.md';

            if (!is_file($taskMdPath)) {
                continue;
            }

            $tasks[] = TaskParser::parse($entry, file_get_contents($taskMdPath));
        }

        return $tasks;
    }

    public function findById(string $huid): ?Task
    {
        $taskMdPath = $this->tasksDir . DIRECTORY_SEPARATOR . $huid . DIRECTORY_SEPARATOR . 'TASK.md';

        if (!is_file($taskMdPath)) {
            return null;
        }

        return TaskParser::parse($huid, file_get_contents($taskMdPath));
    }

    public function create(Task $task): void
    {
        $taskDir = $this->tasksDir . DIRECTORY_SEPARATOR . $task->id;
        mkdir($taskDir);
        file_put_contents($taskDir . DIRECTORY_SEPARATOR . 'TASK.md', TaskRenderer::renderNew($task));
    }

    public function save(Task $task): void
    {
        $taskMdPath = $this->tasksDir . DIRECTORY_SEPARATOR . $task->id . DIRECTORY_SEPARATOR . 'TASK.md';
        file_put_contents($taskMdPath, TaskRenderer::render($task));
    }

    public function loadTagRegistry(): TagRegistry
    {
        $tagsPath = $this->tasksDir . DIRECTORY_SEPARATOR . 'tags';

        if (!is_file($tagsPath)) {
            return TagRegistry::parse('');
        }

        return TagRegistry::parse(file_get_contents($tagsPath));
    }

    public function getTasksDir(): string
    {
        return $this->tasksDir;
    }

    public function getRelativePath(): string
    {
        $cwd = getcwd();
        $relative = str_replace($cwd . DIRECTORY_SEPARATOR, '', $this->tasksDir);

        return str_replace(DIRECTORY_SEPARATOR, '/', $relative);
    }
}
