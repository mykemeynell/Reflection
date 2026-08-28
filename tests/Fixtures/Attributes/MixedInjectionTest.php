<?php

declare(strict_types=1);

namespace Tests\Fixtures\Attributes;

use mykemeynell\Reflection\Attributes\Inject;
use Tests\Fixtures\Container\HttpTransport;

final readonly class MixedInjectionTest
{
    public function __construct(
        #[Inject(HttpTransport::class)]
        public mixed $transport,
    ) {}
}
