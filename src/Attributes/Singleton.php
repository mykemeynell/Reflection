<?php

declare(strict_types=1);

namespace mykemeynell\Reflection\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final class Singleton {}
