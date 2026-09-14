<?php

declare(strict_types=1);

namespace PhpTatr\Tests;

use PhpTatr\Query\OpKind;
use PhpTatr\Query\QueryCompiler;
use PhpTatr\Query\QuerySyntaxError;
use PHPUnit\Framework\TestCase;

final class QueryCompilerTest extends TestCase
{
    public function testCompileTag(): void
    {
        $ops = QueryCompiler::compile(':bug');

        $this->assertCount(1, $ops);
        $this->assertSame(OpKind::TAG, $ops[0]->kind);
        $this->assertSame('bug', $ops[0]->tag);
    }

    public function testCompileAnd(): void
    {
        $ops = QueryCompiler::compile(':bug and :urgent');

        $kinds = array_map(fn ($op) => $op->kind, $ops);
        $this->assertSame([OpKind::TAG, OpKind::TAG, OpKind::AND], $kinds);
    }

    public function testCompileOr(): void
    {
        $ops = QueryCompiler::compile(':bug or :feature');

        $kinds = array_map(fn ($op) => $op->kind, $ops);
        $this->assertSame([OpKind::TAG, OpKind::TAG, OpKind::OR], $kinds);
    }

    public function testCompileNot(): void
    {
        $ops = QueryCompiler::compile('not :bug');

        $kinds = array_map(fn ($op) => $op->kind, $ops);
        $this->assertSame([OpKind::TAG, OpKind::NOT], $kinds);
    }

    public function testCompileAny(): void
    {
        $ops = QueryCompiler::compile('any');

        $this->assertSame(OpKind::ANY, $ops[0]->kind);
    }

    public function testCompileTagged(): void
    {
        $ops = QueryCompiler::compile('tagged');

        $this->assertSame(OpKind::TAGGED, $ops[0]->kind);
    }

    public function testCompilePriorityComparison(): void
    {
        $ops = QueryCompiler::compile('priority lt 50');

        $kinds = array_map(fn ($op) => $op->kind, $ops);
        $this->assertSame([OpKind::PRIORITY, OpKind::INTEGER, OpKind::LT], $kinds);
    }

    public function testCompileGrouping(): void
    {
        $ops = QueryCompiler::compile('[:bug or :feature] and :open');

        $kinds = array_map(fn ($op) => $op->kind, $ops);
        $this->assertSame([OpKind::TAG, OpKind::TAG, OpKind::OR, OpKind::TAG, OpKind::AND], $kinds);
    }

    public function testCompileHuidLiteral(): void
    {
        $ops = QueryCompiler::compile('20260115-143022');

        $this->assertSame(OpKind::ID, $ops[0]->kind);
        $this->assertSame('20260115-143022', $ops[0]->id);
    }

    public function testCompileIntegerLiteral(): void
    {
        $ops = QueryCompiler::compile('priority eq 42');

        $this->assertSame(42, $ops[1]->integer);
    }

    public function testCompileEmptyQuery(): void
    {
        $this->assertSame([], QueryCompiler::compile(''));
        $this->assertSame([], QueryCompiler::compile('   '));
    }

    public function testCompileSyntaxErrorOnUnknownToken(): void
    {
        $this->expectException(QuerySyntaxError::class);
        QueryCompiler::compile('blahblah');
    }

    public function testCompileSyntaxErrorOnUnclosedGroup(): void
    {
        $this->expectException(QuerySyntaxError::class);
        QueryCompiler::compile('[:bug');
    }
}
