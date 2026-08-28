<?php

declare(strict_types=1);

namespace Tests\Fixtures\Attributes;

use mykemeynell\Reflection\Attributes\Inject;
use Tests\Fixtures\Container\Contracts\Transport;
use Tests\Fixtures\Container\HttpTransport;

final class ServiceWithInjectedDependency
{
    public function __construct(
        #[Inject(HttpTransport::class)]
        public Transport $dependency
    ) {}
}
