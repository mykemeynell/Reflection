<?php

declare(strict_types=1);

namespace Tests\Fixtures\Attributes;

use mykemeynell\Reflection\Attributes\Override;
use Tests\Fixtures\Container\Contracts\Transport;

final readonly class OverrideWithoutInjectTest
{
    public function __construct(
        #[Override]
        public Transport $transport,
    ) {}
}
