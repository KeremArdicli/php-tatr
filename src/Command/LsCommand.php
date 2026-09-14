<?php

declare(strict_types=1);

namespace PhpTatr\Command;

use PhpTatr\Core\TaskRenderer;
use PhpTatr\Core\TaskRepository;
use PhpTatr\Query\QueryCompiler;
use PhpTatr\Query\QueryEvaluator;
use PhpTatr\Query\QuerySyntaxError;

final class LsCommand
{
    public function run(array $args): int
    {
        $parsed = ArgParser::parse($args);
        $flags = $parsed['flags'];

        if (isset($flags['help'])) {
            echo "Usage: tatr ls [-c] [-a] [-id] [-debug] [QUERY...]\n";

            return 0;
        }

        $tasksDir = TaskRepository::findTasksDir(getcwd());

        if ($tasksDir === null) {
            fwrite(STDERR, "Could not find tasks/ folder\n");

            return 1;
        }

        $queryString = implode(' ', $parsed['args']);

        try {
            $ops = QueryCompiler::compile($queryString);
        } catch (QuerySyntaxError $e) {
            fwrite(STDERR, "Query error: {$e->getMessage()}\n");

            return 1;
        }

        if (isset($flags['debug'])) {
            foreach ($ops as $op) {
                echo "{$op->kind->value}\n";
            }
        }

        $repo = new TaskRepository($tasksDir);
        $includeClosed = isset($flags['c']);
        $ascending = isset($flags['a']);
        $sortById = isset($flags['id']);

        $filtered = array_values(array_filter(
            $repo->loadAll(),
            function ($task) use ($ops, $includeClosed) {
                if (!$includeClosed && strtoupper($task->status) === 'CLOSED') {
                    return false;
                }

                return QueryEvaluator::matches($ops, $task);
            },
        ));

        usort($filtered, function ($a, $b) use ($sortById) {
            return $sortById ? strcmp($b->id, $a->id) : $b->priority <=> $a->priority;
        });

        if ($ascending) {
            $filtered = array_reverse($filtered);
        }

        if ($filtered === []) {
            echo "No tasks were found\n";

            return 0;
        }

        $relPath = $repo->getRelativePath();

        foreach ($filtered as $task) {
            echo TaskRenderer::renderLine($task, $relPath) . "\n";
        }

        return 0;
    }
}
