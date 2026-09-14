<?php

declare(strict_types=1);

namespace PhpTatr\Query;

use PhpTatr\Core\Task;

final class QueryEvaluator
{
    /**
     * @param list<Op> $ops
     */
    public static function matches(array $ops, Task $task): bool
    {
        if ($ops === []) {
            return true;
        }

        /** @var list<array{type: string, value: bool|int}> $stack */
        $stack = [];

        foreach ($ops as $op) {
            match ($op->kind) {
                OpKind::ANY => $stack[] = ['type' => 'bool', 'value' => true],
                OpKind::TAG => $stack[] = ['type' => 'bool', 'value' => $task->hasTag($op->tag)],
                OpKind::TAGGED => $stack[] = ['type' => 'bool', 'value' => $task->tags !== []],
                OpKind::ID => $stack[] = ['type' => 'bool', 'value' => $task->id === $op->id],
                OpKind::PRIORITY => $stack[] = ['type' => 'int', 'value' => $task->priority],
                OpKind::INTEGER => $stack[] = ['type' => 'int', 'value' => $op->integer],
                OpKind::NOT => $stack[] = ['type' => 'bool', 'value' => !self::popBool($stack)],
                OpKind::AND => self::pushBinaryBool($stack, fn ($a, $b) => $a && $b),
                OpKind::OR => self::pushBinaryBool($stack, fn ($a, $b) => $a || $b),
                OpKind::LT => self::pushComparison($stack, fn ($a, $b) => $a < $b),
                OpKind::GT => self::pushComparison($stack, fn ($a, $b) => $a > $b),
                OpKind::LTE => self::pushComparison($stack, fn ($a, $b) => $a <= $b),
                OpKind::GTE => self::pushComparison($stack, fn ($a, $b) => $a >= $b),
                OpKind::EQ => self::pushComparison($stack, fn ($a, $b) => $a === $b),
                OpKind::NEQ => self::pushComparison($stack, fn ($a, $b) => $a !== $b),
            };
        }

        if (count($stack) !== 1) {
            throw new QueryRuntimeError('Query did not evaluate to a single result');
        }

        $result = $stack[0];

        if ($result['type'] !== 'bool') {
            throw new QueryRuntimeError('Query must evaluate to a boolean');
        }

        return $result['value'];
    }

    /**
     * @param list<array{type: string, value: bool|int}> $stack
     */
    private static function pushBinaryBool(array &$stack, callable $op): void
    {
        $b = self::popBool($stack);
        $a = self::popBool($stack);
        $stack[] = ['type' => 'bool', 'value' => $op($a, $b)];
    }

    /**
     * @param list<array{type: string, value: bool|int}> $stack
     */
    private static function pushComparison(array &$stack, callable $op): void
    {
        $b = self::popInt($stack);
        $a = self::popInt($stack);
        $stack[] = ['type' => 'bool', 'value' => $op($a, $b)];
    }

    /**
     * @param list<array{type: string, value: bool|int}> $stack
     */
    private static function popBool(array &$stack): bool
    {
        if ($stack === []) {
            throw new QueryRuntimeError('Query stack underflow');
        }

        $item = array_pop($stack);

        if ($item['type'] !== 'bool') {
            throw new QueryRuntimeError('Expected a boolean value on the query stack');
        }

        return $item['value'];
    }

    /**
     * @param list<array{type: string, value: bool|int}> $stack
     */
    private static function popInt(array &$stack): int
    {
        if ($stack === []) {
            throw new QueryRuntimeError('Query stack underflow');
        }

        $item = array_pop($stack);

        if ($item['type'] !== 'int') {
            throw new QueryRuntimeError('Expected an integer value on the query stack');
        }

        return $item['value'];
    }
}
