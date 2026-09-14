<?php

declare(strict_types=1);

namespace PhpTatr\Core;

final class Huid
{
    private const PATTERN = '/^\d{8}-\d{6}(-[a-zA-Z0-9\-]+)?$/';

    private const SCAN_PATTERN = '/\d{8}-\d{6}(-[a-zA-Z0-9\-]+)?/';

    public static function isValid(string $id): bool
    {
        return (bool) preg_match(self::PATTERN, $id);
    }

    public static function generate(?string $suffix = null): string
    {
        $base = gmdate('Ymd-His');

        return $suffix !== null ? "{$base}-{$suffix}" : $base;
    }

    /**
     * Scan a string for the first embedded HUID.
     *
     * @return array{0: string, 1: string}|null [huid, remainingContentAfterMatch]
     */
    public static function extractFirst(string $content): ?array
    {
        if (!preg_match(self::SCAN_PATTERN, $content, $matches, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $huid = $matches[0][0];
        $endOffset = $matches[0][1] + strlen($huid);

        return [$huid, substr($content, $endOffset)];
    }
}
