<?php

declare(strict_types=1);

namespace Tests\Fixtures\Container;

final readonly class ServiceWithDependency
{
    public function __construct(
        public SimpleDependency $dependency,
    ) {}
}
