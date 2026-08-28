<?php

declare(strict_types=1);

use mykemeynell\Reflection\Concerns\InteractsWithAttributes;
use mykemeynell\Reflection\Concerns\InteractsWithReflection;
use mykemeynell\Reflection\Concerns\NormalizesParameters;

arch('attribute processing is isolated')
    ->expect(InteractsWithAttributes::class)
    ->not->toUse([InteractsWithReflection::class, NormalizesParameters::class]);

arch('reflection processing is isolated')
    ->expect(InteractsWithReflection::class)
    ->not->toUse([InteractsWithAttributes::class, NormalizesParameters::class]);

arch('parameter processing is isolated')
    ->expect(NormalizesParameters::class)
    ->not->toUse([InteractsWithAttributes::class, InteractsWithReflection::class]);
