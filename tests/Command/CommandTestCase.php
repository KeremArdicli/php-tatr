<?php

declare(strict_types=1);

namespace PhpTatr\Tests\Command;

use PHPUnit\Framework\TestCase;

abstract class CommandTestCase extends TestCase
{
    protected string $tmpDir;

    private string $originalCwd;

    protected function setUp(): void
    {
        $this->originalCwd = getcwd();
        $this->tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('php-tatr-cmd-test-', true);
        mkdir($this->tmpDir, recursive: true);
        chdir($this->tmpDir);
    }

    protected function tearDown(): void
    {
        chdir($this->originalCwd);
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

    /**
     * @return array{0: int, 1: string} [exitCode, capturedOutput]
     */
    protected function runCommand(object $command, array $args): array
    {
        ob_start();
        $exitCode = $command->run($args);
        $output = ob_get_clean();

        return [$exitCode, $output];
    }
}
