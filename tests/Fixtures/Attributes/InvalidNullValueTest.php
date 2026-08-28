<?php

declare(strict_types=1);

namespace Tests\Fixtures\Attributes;

use mykemeynell\Reflection\Attributes\Value;

final readonly class InvalidNullValueTest
{
    public function __construct(
        #[Value('invalid')]
        public null $value,
    ) {}
}
