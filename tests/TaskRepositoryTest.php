<?php

declare(strict_types=1);

namespace PhpTatr\Tests;

use PhpTatr\Core\Task;
use PhpTatr\Core\TaskRepository;
use PHPUnit\Framework\TestCase;

final class TaskRepositoryTest extends TestCase
{
    private string $tmpDir;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('php-tatr-test-', true);
        mkdir($this->tmpDir, recursive: true);
    }

    protected function tearDown(): void
    {
        $this->removeDir($this->tmpDir);
    }

    private function removeDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        foreach (scandir($dir) as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $dir . DIRECTORY_SEPARATOR . $entry;

            if (is_dir($path)) {
                $this->removeDir($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    public function testFindTasksDirFromSubdirectory(): void
    {
        $tasksDir = TaskRepository::init($this->tmpDir, withReadme: false);
        $subDir = $this->tmpDir . DIRECTORY_SEPARATOR . 'nested' . DIRECTORY_SEPARATOR . 'deep';
        mkdir($subDir, recursive: true);

        $found = TaskRepository::findTasksDir($subDir);

        $this->assertSame(realpath($tasksDir), $found);
    }

    public function testFindTasksDirReturnsNullWhenNotFound(): void
    {
        $found = TaskRepository::findTasksDir(sys_get_temp_dir());

        $this->assertNull($found);
    }

    public function testLoadAllEmpty(): void
    {
        $tasksDir = TaskRepository::init($this->tmpDir, withReadme: false);
        $repo = new TaskRepository($tasksDir);

        $this->assertSame([], $repo->loadAll());
    }

    public function testCreateAndLoadTask(): void
    {
        $tasksDir = TaskRepository::init($this->tmpDir, withReadme: false);
        $repo = new TaskRepository($tasksDir);

        $task = new Task(id: '20260115-143022', title: 'Test task', priority: 50, tags: ['bug']);
        $repo->create($task);

        $tasks = $repo->loadAll();

        $this->assertCount(1, $tasks);
        $this->assertSame('Test task', $tasks[0]->title);
        $this->assertSame(['bug'], $tasks[0]->tags);
    }

    public function testFindByIdExact(): void
    {
        $tasksDir = TaskRepository::init($this->tmpDir, withReadme: false);
        $repo = new TaskRepository($tasksDir);
        $repo->create(new Task(id: '20260115-143022', title: 'Findable'));

        $found = $repo->findById('20260115-143022');

        $this->assertNotNull($found);
        $this->assertSame('Findable', $found->title);
    }

    public function testFindByIdNotFound(): void
    {
        $tasksDir = TaskRepository::init($this->tmpDir, withReadme: false);
        $repo = new TaskRepository($tasksDir);

        $this->assertNull($repo->findById('20260101-000000'));
    }

    public function testSavePreservesBody(): void
    {
        $tasksDir = TaskRepository::init($this->tmpDir, withReadme: false);
        $repo = new TaskRepository($tasksDir);
        $repo->create(new Task(id: '20260115-143022', title: 'Test', tags: ['bug', 'urgent']));

        $task = $repo->findById('20260115-143022');
        $updated = $task->withoutTags(['urgent']);
        $repo->save($updated);

        $reloaded = $repo->findById('20260115-143022');

        $this->assertSame(['bug'], $reloaded->tags);
        $this->assertStringContainsString('No description.', $reloaded->body);
    }
}
