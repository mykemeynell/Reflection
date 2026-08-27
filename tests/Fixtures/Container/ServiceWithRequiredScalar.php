<?php

declare(strict_types=1);

namespace Tests\Fixtures\Container;

final readonly class ServiceWithRequiredScalar
{
    public function __construct(
        public int $timeout,
    ) {}
}
