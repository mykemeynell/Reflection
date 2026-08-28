<?php

declare(strict_types=1);

namespace Tests\Fixtures\Attributes;

use mykemeynell\Reflection\Attributes\Value;

final readonly class ValueTypeTest
{
    public function __construct(
        #[Value(null)]
        public ?int $nullable,
        #[Value('ten')]
        public int|string $union,
    ) {}
}
