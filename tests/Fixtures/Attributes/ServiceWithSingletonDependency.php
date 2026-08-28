<?php

declare(strict_types=1);

namespace Tests\Fixtures\Attributes;

final class ServiceWithSingletonDependency
{
    public function __construct(
        public SingletonPrecedenceTest $dependency
    ) {}
}
