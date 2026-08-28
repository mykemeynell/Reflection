<?php

declare(strict_types=1);

namespace Tests\Fixtures\Attributes;

use mykemeynell\Reflection\Attributes\Inject;
use mykemeynell\Reflection\Attributes\Value;
use Tests\Fixtures\Container\HttpTransport;

final readonly class ConflictingAttributesTest
{
    public function __construct(
        #[Inject(HttpTransport::class), Value(null)]
        public mixed $dependency,
    ) {}
}
