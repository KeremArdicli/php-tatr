<?php

declare(strict_types=1);

namespace PhpTatr\Tests;

use PhpTatr\Core\Task;
use PhpTatr\Query\QueryCompiler;
use PhpTatr\Query\QueryEvaluator;
use PHPUnit\Framework\TestCase;

final class QueryEvaluatorTest extends TestCase
{
    private function task(array $overrides = []): Task
    {
        return new Task(
            id: $overrides['id'] ?? '20260115-143022',
            title: $overrides['title'] ?? 'Title',
            status: $overrides['status'] ?? 'OPEN',
            priority: $overrides['priority'] ?? 100,
            tags: $overrides['tags'] ?? [],
        );
    }

    public function testEmptyQueryMatchesAll(): void
    {
        $this->assertTrue(QueryEvaluator::matches([], $this->task()));
    }

    public function testTagMatch(): void
    {
        $ops = QueryCompiler::compile(':bug');
        $this->assertTrue(QueryEvaluator::matches($ops, $this->task(['tags' => ['bug']])));
    }

    public function testTagMismatch(): void
    {
        $ops = QueryCompiler::compile(':bug');
        $this->assertFalse(QueryEvaluator::matches($ops, $this->task(['tags' => ['ui']])));
    }

    public function testAny(): void
    {
        $ops = QueryCompiler::compile('any');
        $this->assertTrue(QueryEvaluator::matches($ops, $this->task()));
    }

    public function testTagged(): void
    {
        $ops = QueryCompiler::compile('tagged');
        $this->assertTrue(QueryEvaluator::matches($ops, $this->task(['tags' => ['bug']])));
        $this->assertFalse(QueryEvaluator::matches($ops, $this->task(['tags' => []])));
    }

    public function testNot(): void
    {
        $ops = QueryCompiler::compile('not :bug');
        $this->assertTrue(QueryEvaluator::matches($ops, $this->task(['tags' => []])));
        $this->assertFalse(QueryEvaluator::matches($ops, $this->task(['tags' => ['bug']])));
    }

    public function testAnd(): void
    {
        $ops = QueryCompiler::compile(':bug and :urgent');
        $this->assertTrue(QueryEvaluator::matches($ops, $this->task(['tags' => ['bug', 'urgent']])));
        $this->assertFalse(QueryEvaluator::matches($ops, $this->task(['tags' => ['bug']])));
    }

    public function testOr(): void
    {
        $ops = QueryCompiler::compile(':bug or :feature');
        $this->assertTrue(QueryEvaluator::matches($ops, $this->task(['tags' => ['feature']])));
        $this->assertFalse(QueryEvaluator::matches($ops, $this->task(['tags' => ['ui']])));
    }

    public function testPriorityLt(): void
    {
        $ops = QueryCompiler::compile('priority lt 100');
        $this->assertTrue(QueryEvaluator::matches($ops, $this->task(['priority' => 50])));
        $this->assertFalse(QueryEvaluator::matches($ops, $this->task(['priority' => 150])));
    }

    public function testPriorityGe(): void
    {
        $ops = QueryCompiler::compile('priority ge 50');
        $this->assertTrue(QueryEvaluator::matches($ops, $this->task(['priority' => 50])));
        $this->assertFalse(QueryEvaluator::matches($ops, $this->task(['priority' => 49])));
    }

    public function testIdMatch(): void
    {
        $ops = QueryCompiler::compile('20260115-143022');
        $this->assertTrue(QueryEvaluator::matches($ops, $this->task(['id' => '20260115-143022'])));
        $this->assertFalse(QueryEvaluator::matches($ops, $this->task(['id' => '20260101-000000'])));
    }

    public function testComplexQuery(): void
    {
        $ops = QueryCompiler::compile('[:bug or :feature] and priority lt 50');
        $this->assertTrue(QueryEvaluator::matches($ops, $this->task(['tags' => ['bug'], 'priority' => 10])));
        $this->assertFalse(QueryEvaluator::matches($ops, $this->task(['tags' => ['bug'], 'priority' => 100])));
        $this->assertFalse(QueryEvaluator::matches($ops, $this->task(['tags' => ['ui'], 'priority' => 10])));
    }
}
