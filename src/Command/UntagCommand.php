<?php

declare(strict_types=1);

namespace PhpTatr\Command;

use PhpTatr\Core\TaskParser;
use PhpTatr\Core\TaskRepository;
use PhpTatr\Query\QueryCompiler;
use PhpTatr\Query\QueryEvaluator;
use PhpTatr\Query\QuerySyntaxError;

final class UntagCommand
{
    public function run(array $args): int
    {
        $tagsToRemove = [];
        $queryTokens = [];
        $includeClosed = false;
        $count = count($args);

        for ($i = 0; $i < $count; $i++) {
            $token = $args[$i];

            if ($token === '-t' || $token === '--t') {
                $value = $args[++$i] ?? '';
                array_push($tagsToRemove, ...TaskParser::parseTags($value));
                continue;
            }

            if ($token === '-c' || $token === '--c') {
                $includeClosed = true;
                continue;
            }

            if ($token === '-help' || $token === '--help') {
                echo "Usage: tatr untag -t tag [-t tag...] [-c] [QUERY...]\n";

                return 0;
            }

            $queryTokens[] = $token;
        }

        if ($tagsToRemove === []) {
            fwrite(STDERR, "At least one -t <tag> must be provided\n");

            return 1;
        }

        $tasksDir = TaskRepository::findTasksDir(getcwd());

        if ($tasksDir === null) {
            fwrite(STDERR, "Could not find tasks/ folder\n");

            return 1;
        }

        try {
            $ops = QueryCompiler::compile(implode(' ', $queryTokens));
        } catch (QuerySyntaxError $e) {
            fwrite(STDERR, "Query error: {$e->getMessage()}\n");

            return 1;
        }

        $repo = new TaskRepository($tasksDir);
        $updated = 0;

        foreach ($repo->loadAll() as $task) {
            if (!$includeClosed && strtoupper($task->status) === 'CLOSED') {
                continue;
            }

            if (!QueryEvaluator::matches($ops, $task)) {
                continue;
            }

            if (array_intersect($task->tags, $tagsToRemove) === []) {
                continue;
            }

            $repo->save($task->withoutTags($tagsToRemove));
            $updated++;
        }

        echo "{$updated} tasks updated\n";

        return 0;
    }
}
