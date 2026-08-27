<?php

declare(strict_types=1);

namespace Tests\Fixtures\Container;

final readonly class Configuration
{
    public function __construct(
        public string $value,
    ) {}
}
