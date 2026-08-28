<?php

declare(strict_types=1);

namespace Tests\Fixtures\Attributes;

use mykemeynell\Reflection\Attributes\Value;

final readonly class InvalidValueTest
{
    public function __construct(
        #[Value('ten')]
        public int $value,
    ) {}
}
