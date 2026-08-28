<?php

declare(strict_types=1);

use mykemeynell\Reflection\Application\Container;
use mykemeynell\Reflection\Exceptions\ContainerException;
use Tests\Fixtures\Attributes\MissingInjectionTest;
use Tests\Fixtures\Attributes\MixedInjectionTest;
use Tests\Fixtures\Attributes\ServiceWithInjectedDependency;
use Tests\Fixtures\Container\HttpTransport;

beforeEach(function (): void {
    $this->container = new Container;
});

it('resolves a dependency specified by Inject', function (): void {
    $service = $this->container->make(
        ServiceWithInjectedDependency::class,
    );

    expect($service->dependency)
        ->toBeInstanceOf(HttpTransport::class);
});

it('throws when Inject targets a missing service', function (): void {
    $this->container->make(MissingInjectionTest::class);
})->throws(ContainerException::class, 'Inject target');

it('resolves Inject on a mixed parameter', function (): void {
    $service = $this->container->make(MixedInjectionTest::class);

    expect($service->transport)->toBeInstanceOf(HttpTransport::class);
});
