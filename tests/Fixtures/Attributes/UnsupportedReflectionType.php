<?php

declare(strict_types=1);

namespace Tests\Fixtures\Attributes;

use ReflectionType;

final class UnsupportedReflectionType extends ReflectionType
{
    public function allowsNull(): bool
    {
        return false;
    }

    public function __toString(): string
    {
        return 'unsupported';
    }
}
