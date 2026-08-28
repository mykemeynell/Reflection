<?php

declare(strict_types=1);

use mykemeynell\Reflection\Application\Container;
use mykemeynell\Reflection\Exceptions\ContainerException;
use Tests\Fixtures\Attributes\ConflictingAttributesTest;
use Tests\Fixtures\Attributes\OverrideWithoutInjectTest;

it('rejects Override without Inject', function (): void {
    (new Container)->make(OverrideWithoutInjectTest::class);
})->throws(ContainerException::class, 'Override requires Inject');

it('rejects conflicting parameter attributes', function (): void {
    (new Container)->make(ConflictingAttributesTest::class);
})->throws(ContainerException::class, 'Inject and Value');
