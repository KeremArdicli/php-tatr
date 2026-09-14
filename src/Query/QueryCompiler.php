<?php

declare(strict_types=1);

namespace PhpTatr\Query;

use PhpTatr\Core\Huid;

final class QueryCompiler
{
    private const COMPARE_OPS = [
        'lt' => OpKind::LT,
        'le' => OpKind::LTE,
        'gt' => OpKind::GT,
        'ge' => OpKind::GTE,
        'eq' => OpKind::EQ,
        'ne' => OpKind::NEQ,
    ];

    /** @var list<string> */
    private array $tokens;

    private int $pos = 0;

    /** @var list<Op> */
    private array $ops = [];

    /**
     * @return list<Op>
     */
    public static function compile(string $source): array
    {
        $tokens = self::tokenize($source);

        if ($tokens === []) {
            return [];
        }

        $compiler = new self($tokens);
        $compiler->compileOr();

        if ($compiler->pos < count($compiler->tokens)) {
            throw new QuerySyntaxError("Unexpected token: {$compiler->tokens[$compiler->pos]}");
        }

        return $compiler->ops;
    }

    /**
     * @param list<string> $tokens
     */
    private function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * @return list<string>
     */
    private static function tokenize(string $source): array
    {
        $tokens = [];
        $len = strlen($source);
        $i = 0;

        while ($i < $len) {
            $char = $source[$i];

            if (ctype_space($char)) {
                $i++;
                continue;
            }

            if ($char === '[' || $char === ']') {
                $tokens[] = $char;
                $i++;
                continue;
            }

            $start = $i;

            while ($i < $len && !ctype_space($source[$i]) && $source[$i] !== '[' && $source[$i] !== ']') {
                $i++;
            }

            $tokens[] = substr($source, $start, $i - $start);
        }

        return $tokens;
    }

    private function peek(): ?string
    {
        return $this->tokens[$this->pos] ?? null;
    }

    private function next(): ?string
    {
        return $this->tokens[$this->pos++] ?? null;
    }

    private function match(string $expected): bool
    {
        if ($this->peek() === $expected) {
            $this->pos++;

            return true;
        }

        return false;
    }

    private function emit(Op $op): void
    {
        $this->ops[] = $op;
    }

    private function compileOr(): void
    {
        $this->compileAnd();

        while ($this->match('or')) {
            $this->compileAnd();
            $this->emit(new Op(OpKind::OR));
        }
    }

    private function compileAnd(): void
    {
        $this->compileCompare();

        while ($this->match('and')) {
            $this->compileCompare();
            $this->emit(new Op(OpKind::AND));
        }
    }

    private function compileCompare(): void
    {
        $this->compilePrimary();

        while (($token = $this->peek()) !== null && isset(self::COMPARE_OPS[$token])) {
            $this->next();
            $this->compilePrimary();
            $this->emit(new Op(self::COMPARE_OPS[$token]));
        }
    }

    private function compilePrimary(): void
    {
        $token = $this->next();

        if ($token === null) {
            throw new QuerySyntaxError('Expected an expression but reached end of query');
        }

        if ($token === 'not') {
            $this->compilePrimary();
            $this->emit(new Op(OpKind::NOT));

            return;
        }

        if ($token === 'any') {
            $this->emit(new Op(OpKind::ANY));

            return;
        }

        if ($token === 'tagged') {
            $this->emit(new Op(OpKind::TAGGED));

            return;
        }

        if ($token === 'priority') {
            $this->emit(new Op(OpKind::PRIORITY));

            return;
        }

        if ($token === '[') {
            $this->compileOr();

            if (!$this->match(']')) {
                throw new QuerySyntaxError("Expected ']' to close group");
            }

            return;
        }

        if (str_starts_with($token, ':')) {
            $this->compileTag($token);

            return;
        }

        if (preg_match('/^-?\d+$/', $token) === 1) {
            $this->emit(new Op(OpKind::INTEGER, integer: (int) $token));

            return;
        }

        if (Huid::isValid($token)) {
            $this->emit(new Op(OpKind::ID, id: $token));

            return;
        }

        throw new QuerySyntaxError("Unknown token in query: {$token}");
    }

    private function compileTag(string $token): void
    {
        $tag = substr($token, 1);

        if ($tag === '') {
            throw new QuerySyntaxError('Empty tag in query');
        }

        $this->emit(new Op(OpKind::TAG, tag: $tag));
    }
}
