<?php

declare(strict_types=1);

namespace Tests\Fixtures\Container\Circular;

final readonly class CircularA
{
    public function __construct(
        public CircularB $dependency,
    ) {}
}
