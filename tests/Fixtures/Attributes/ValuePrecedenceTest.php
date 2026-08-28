<?php

declare(strict_types=1);

namespace Tests\Fixtures\Attributes;

use mykemeynell\Reflection\Attributes\Value;

final readonly class ValuePrecedenceTest
{
    public function __construct(
        #[Value(10)]
        public int $value = 20,
    ) {}
}
