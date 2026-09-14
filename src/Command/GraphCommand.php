<?php

declare(strict_types=1);

namespace PhpTatr\Command;

use PhpTatr\Core\Huid;
use PhpTatr\Core\TaskRepository;

final class GraphCommand
{
    public function run(array $args): int
    {
        $parsed = ArgParser::parse($args);
        $flags = $parsed['flags'];

        if (isset($flags['help'])) {
            echo "Usage: tatr graph\n";

            return 0;
        }

        $tasksDir = TaskRepository::findTasksDir(getcwd());

        if ($tasksDir === null) {
            fwrite(STDERR, "Could not find tasks/ folder\n");

            return 1;
        }

        $repo = new TaskRepository($tasksDir);
        $tasks = $repo->loadAll();
        $ids = [];

        foreach ($tasks as $task) {
            $ids[$task->id] = true;
        }

        $edges = [];

        foreach ($tasks as $task) {
            $remaining = $task->rawContent;

            while (($extracted = Huid::extractFirst($remaining)) !== null) {
                [$huid, $remaining] = $extracted;

                if ($huid === $task->id || !isset($ids[$huid])) {
                    continue;
                }

                $edges["{$task->id}->{$huid}"] = [$task->id, $huid];
            }
        }

        $lines = ["digraph tasks {"];

        foreach ($tasks as $task) {
            $label = addslashes($task->title);
            $lines[] = "    \"{$task->id}\" [label=\"{$label}\"];";
        }

        foreach ($edges as [$from, $to]) {
            $lines[] = "    \"{$from}\" -> \"{$to}\";";
        }

        $lines[] = '}';
        $dot = implode("\n", $lines) . "\n";

        $dotPath = getcwd() . DIRECTORY_SEPARATOR . 'graph.dot';
        file_put_contents($dotPath, $dot);
        echo "Generated {$dotPath}\n";

        $devNull = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
        $lookupCommand = PHP_OS_FAMILY === 'Windows' ? 'where neato' : 'command -v neato';
        exec("{$lookupCommand} > {$devNull} 2>&1", result_code: $lookupResultCode);

        if ($lookupResultCode !== 0) {
            echo "graphviz (neato) not found, skipping SVG render\n";

            return 0;
        }

        $svgPath = getcwd() . DIRECTORY_SEPARATOR . 'graph.svg';
        $escapedDot = escapeshellarg($dotPath);
        $escapedSvg = escapeshellarg($svgPath);
        exec("neato -Goverlap=scale -Tsvg {$escapedDot} -o {$escapedSvg}", result_code: $resultCode);

        if ($resultCode === 0) {
            echo "Generated {$svgPath}\n";
        } else {
            fwrite(STDERR, "neato failed to render SVG\n");
        }

        return 0;
    }
}
