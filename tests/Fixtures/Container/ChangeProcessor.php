<?php

declare(strict_types=1);

namespace Tests\Fixtures\Container;

final readonly class ChangeProcessor
{
    public function __construct(
        public SpecificServiceDispatcher $dispatcher,
    ) {}
}
