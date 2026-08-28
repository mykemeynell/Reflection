<?php

declare(strict_types=1);

namespace Tests\Fixtures\Attributes;

use Tests\Fixtures\Container\Contracts\Transport;

final readonly class OptionalInterfaceDependencyTest
{
    public function __construct(
        public ?Transport $transport = null,
    ) {}
}
