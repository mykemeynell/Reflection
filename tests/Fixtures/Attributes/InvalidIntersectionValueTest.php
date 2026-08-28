<?php

declare(strict_types=1);

namespace Tests\Fixtures\Attributes;

use mykemeynell\Reflection\Attributes\Value;

final readonly class InvalidIntersectionValueTest
{
    public function __construct(
        #[Value(new PartialIntersectionValue)]
        public FirstValueContract&SecondValueContract $value,
    ) {}
}
