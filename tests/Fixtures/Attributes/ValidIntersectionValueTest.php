<?php

declare(strict_types=1);

namespace Tests\Fixtures\Attributes;

use mykemeynell\Reflection\Attributes\Value;

final readonly class ValidIntersectionValueTest
{
    public function __construct(
        #[Value(new IntersectionValue)]
        public FirstValueContract&SecondValueContract $value,
    ) {}
}
