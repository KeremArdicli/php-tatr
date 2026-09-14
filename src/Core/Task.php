<?php

declare(strict_types=1);

namespace PhpTatr\Core;

final readonly class Task
{
    /**
     * @param list<string>          $tags
     * @param array<string, string> $properties
     */
    public function __construct(
        public string $id,
        public string $title,
        public string $status = 'OPEN',
        public int $priority = 999999,
        public array $tags = [],
        public array $properties = [],
        public string $body = '',
        public string $rawContent = '',
    ) {
    }

    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags, true);
    }

    /**
     * @param list<string> $tagsToRemove
     */
    public function withoutTags(array $tagsToRemove): self
    {
        return new self(
            id: $this->id,
            title: $this->title,
            status: $this->status,
            priority: $this->priority,
            tags: array_values(array_diff($this->tags, $tagsToRemove)),
            properties: $this->properties,
            body: $this->body,
            rawContent: $this->rawContent,
        );
    }
}
