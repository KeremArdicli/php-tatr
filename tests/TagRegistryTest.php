<?php

declare(strict_types=1);

namespace PhpTatr\Tests;

use PhpTatr\Core\TagRegistry;
use PHPUnit\Framework\TestCase;

final class TagRegistryTest extends TestCase
{
    public function testParsesSpaceSeparatedFormat(): void
    {
        $registry = TagRegistry::parse('bug unintended behavior of the system');

        $this->assertSame('unintended behavior of the system', $registry->getDescription('bug'));
    }

    public function testParsesCommaSeparatedFormat(): void
    {
        $registry = TagRegistry::parse('bug , unintended behavior');

        $this->assertSame('unintended behavior', $registry->getDescription('bug'));
    }

    public function testHandlesTagsWithoutDescription(): void
    {
        $registry = TagRegistry::parse('wontfix');

        $this->assertSame('', $registry->getDescription('wontfix'));
    }

    public function testSkipsBlankLines(): void
    {
        $registry = TagRegistry::parse("bug , fix\n\n\nfeature , new stuff");

        $this->assertCount(2, $registry->all());
    }

    public function testReturnsNullForUnknownTag(): void
    {
        $registry = TagRegistry::parse('bug , fix');

        $this->assertNull($registry->getDescription('unknown'));
    }
}
