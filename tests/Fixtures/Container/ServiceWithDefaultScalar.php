<?php

declare(strict_types=1);

namespace Tests\Fixtures\Container;

final readonly class ServiceWithDefaultScalar
{
    public function __construct(
        public int $timeout = 30,
    ) {}
}
