<?php

declare(strict_types=1);

namespace PhpTatr\Command;

final class VersionCommand
{
    public function run(array $args): int
    {
        $commit = trim((string) shell_exec('git rev-parse --short HEAD 2>&1'));

        if ($commit === '' || str_contains($commit, 'fatal')) {
            $commit = 'unknown';
        }

        echo "tatr (php-tatr) commit {$commit}, PHP " . PHP_VERSION . "\n";

        return 0;
    }
}
