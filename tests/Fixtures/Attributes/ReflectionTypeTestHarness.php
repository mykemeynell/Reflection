<?php

declare(strict_types=1);

namespace Tests\Fixtures\Attributes;

use mykemeynell\Reflection\Concerns\InteractsWithReflection;

final class ReflectionTypeTestHarness
{
    use InteractsWithReflection {
        typeAcceptsValue as public accepts;
    }
}
