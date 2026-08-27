<?php

declare(strict_types=1);

namespace Tests\Fixtures\Container;

use Tests\Fixtures\Container\Contracts\Transport;

final readonly class ConfiguredTransport implements Transport
{
    public function __construct(
        public Configuration $configuration,
    ) {}
}
