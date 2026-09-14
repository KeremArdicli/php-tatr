<?php

declare(strict_types=1);

namespace PhpTatr\Core;

final class TagRegistry
{
    /** @var array<string, string> */
    private array $tags = [];

    public static function parse(string $content): self
    {
        $registry = new self();

        foreach (explode("\n", $content) as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if (preg_match('/^(\S+)[\s,]+(.+)$/', $line, $matches)) {
                $registry->tags[$matches[1]] = trim($matches[2]);
            } else {
                $registry->tags[rtrim($line, ',')] = '';
            }
        }

        return $registry;
    }

    public function getDescription(string $tag): ?string
    {
        return $this->tags[$tag] ?? null;
    }

    /** @return array<string, string> */
    public function all(): array
    {
        return $this->tags;
    }
}
