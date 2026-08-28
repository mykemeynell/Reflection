<?php

declare(strict_types=1);

namespace Tests\Fixtures\Attributes;

use mykemeynell\Reflection\Attributes\Inject;

final readonly class AutowirePrecedenceTest
{
    public function __construct(
        #[Inject(InjectedService::class)]
        public AutowireableService $service,
    ) {}
}
