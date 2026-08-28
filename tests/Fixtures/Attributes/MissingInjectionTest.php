<?php

declare(strict_types=1);

namespace Tests\Fixtures\Attributes;

use mykemeynell\Reflection\Attributes\Inject;
use Tests\Fixtures\Container\Contracts\Transport;

final readonly class MissingInjectionTest
{
    public function __construct(
        #[Inject('Tests\\Fixtures\\Container\\MissingService')]
        public Transport $transport,
    ) {}
}
