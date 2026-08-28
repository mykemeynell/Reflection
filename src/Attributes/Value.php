<?php

declare(strict_types=1);

namespace mykemeynell\Reflection\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER)]
final readonly class Value
{
    public function __construct(
        public mixed $value,
    ) {}
}
