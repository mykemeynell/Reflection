<?php

declare(strict_types=1);

namespace Tests\Fixtures\Container\Circular;

final readonly class CircularB
{
    public function __construct(
        public CircularA $dependency,
    ) {}
}
