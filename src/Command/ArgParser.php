<?php

declare(strict_types=1);

namespace PhpTatr\Command;

final class ArgParser
{
    /**
     * Parse CLI arguments into flags and positional arguments.
     *
     * Supports: -flag, --flag, -flag value, --flag value, --flag=value
     *
     * @param list<string> $argv
     * @param list<string> $flagsWithValues Flag names (without leading dashes) that consume the next argument as their value
     *
     * @return array{flags: array<string, string|true>, args: list<string>}
     */
    public static function parse(array $argv, array $flagsWithValues = []): array
    {
        $flags = [];
        $args = [];
        $count = count($argv);

        for ($i = 0; $i < $count; $i++) {
            $token = $argv[$i];

            if (!str_starts_with($token, '-') || $token === '-') {
                $args[] = $token;
                continue;
            }

            $name = ltrim($token, '-');

            if (str_contains($name, '=')) {
                [$name, $value] = explode('=', $name, 2);
                $flags[$name] = $value;
                continue;
            }

            if (in_array($name, $flagsWithValues, true) && $i + 1 < $count) {
                $flags[$name] = $argv[++$i];
                continue;
            }

            $flags[$name] = true;
        }

        return ['flags' => $flags, 'args' => $args];
    }
}
