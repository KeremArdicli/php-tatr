<?php

declare(strict_types=1);

namespace PhpTatr\Query;

final readonly class Op
{
    public function __construct(
        public OpKind $kind,
        public ?string $tag = null,
        public ?string $id = null,
        public ?int $integer = null,
    ) {
    }
}
