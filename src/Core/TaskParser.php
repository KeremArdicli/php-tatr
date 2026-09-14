<?php

declare(strict_types=1);

namespace PhpTatr\Core;

final class TaskParser
{
    private const INVALID_TITLE = '!!! INVALID: TASK TITLE MUST START WITH # !!!';

    public static function parse(string $id, string $content): Task
    {
        $lines = explode("\n", $content);

        $offset = 0;
        $title = self::extractTitle($lines, $offset);
        $properties = self::extractProperties($lines, $offset);
        $body = self::extractBody($lines, $offset);

        $status = $properties['STATUS'] ?? 'OPEN';
        $priority = isset($properties['PRIORITY']) ? (int) $properties['PRIORITY'] : 999999;
        $tags = isset($properties['TAGS']) ? self::parseTags($properties['TAGS']) : [];

        return new Task(
            id: $id,
            title: $title,
            status: $status,
            priority: $priority,
            tags: $tags,
            properties: $properties,
            body: $body,
            rawContent: $content,
        );
    }

    /**
     * @param list<string> $lines
     */
    private static function extractTitle(array $lines, int &$offset): string
    {
        foreach ($lines as $index => $line) {
            if (preg_match('/^#\s*(.*)$/', $line, $matches)) {
                $offset = $index + 1;

                return trim($matches[1]);
            }
        }

        $offset = 0;

        return self::INVALID_TITLE;
    }

    /**
     * @param list<string> $lines
     *
     * @return array<string, string>
     */
    private static function extractProperties(array $lines, int &$offset): array
    {
        while (isset($lines[$offset]) && trim($lines[$offset]) === '') {
            $offset++;
        }

        $properties = [];

        while (isset($lines[$offset]) && preg_match('/^-\s*([A-Za-z0-9]+)\s*:\s*(.*)$/', $lines[$offset], $matches)) {
            $properties[$matches[1]] = trim($matches[2]);
            $offset++;
        }

        return $properties;
    }

    /**
     * @param list<string> $lines
     */
    private static function extractBody(array $lines, int $offset): string
    {
        return implode("\n", array_slice($lines, $offset));
    }

    /**
     * @return list<string>
     */
    public static function parseTags(string $tagString): array
    {
        return preg_split('/[\s,]+/', trim($tagString), -1, PREG_SPLIT_NO_EMPTY);
    }
}
