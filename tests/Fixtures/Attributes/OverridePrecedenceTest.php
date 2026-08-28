<?php

declare(strict_types=1);

namespace Tests\Fixtures\Attributes;

use mykemeynell\Reflection\Attributes\Inject;
use mykemeynell\Reflection\Attributes\Override;
use Tests\Fixtures\Container\Contracts\Transport;
use Tests\Fixtures\Container\HttpTransport;

final readonly class OverridePrecedenceTest
{
    public function __construct(
        #[Inject(HttpTransport::class), Override]
        public Transport $transport,
    ) {}
}
