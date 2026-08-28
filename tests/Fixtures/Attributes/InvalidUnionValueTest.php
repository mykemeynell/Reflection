<?php

declare(strict_types=1);

namespace Tests\Fixtures\Attributes;

use mykemeynell\Reflection\Attributes\Value;

final readonly class InvalidUnionValueTest
{
    public function __construct(
        #[Value(false)]
        public int|string $value,
    ) {}
}
