<?php

declare(strict_types=1);

namespace Tests\Fixtures\Container;

final readonly class ServiceWithMixedParameters
{
    public function __construct(
        public SimpleDependency $dependency,
        public int $timeout,
        public string $name = 'default',
    ) {}
}
