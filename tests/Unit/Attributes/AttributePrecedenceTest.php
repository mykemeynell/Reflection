<?php

declare(strict_types=1);

use mykemeynell\Reflection\Application\Container;
use Tests\Fixtures\Attributes\AttributePrecedenceTest;
use Tests\Fixtures\Attributes\AutowirePrecedenceTest;
use Tests\Fixtures\Attributes\InjectedService;
use Tests\Fixtures\Attributes\OptionalInterfaceDependencyTest;
use Tests\Fixtures\Container\Contracts\Transport;
use Tests\Fixtures\Container\HttpTransport;
use Tests\Fixtures\Container\SpecificTransportService;

beforeEach(function (): void {
    $this->container = new Container;
});

it('prefers a runtime argument over Inject', function (): void {
    $transport = new SpecificTransportService;

    $service = $this->container->make(AttributePrecedenceTest::class, transport: $transport);

    expect($service->transport)->toBe($transport);
});

it('prefers a contextual binding over Inject', function (): void {
    $this->container->when(AttributePrecedenceTest::class)
        ->needs(Transport::class)
        ->give(SpecificTransportService::class);

    $service = $this->container->make(AttributePrecedenceTest::class);

    expect($service->transport)->toBeInstanceOf(SpecificTransportService::class);
});

it('prefers a global binding over Inject', function (): void {
    $this->container->bind(Transport::class, SpecificTransportService::class);

    $service = $this->container->make(AttributePrecedenceTest::class);

    expect($service->transport)->toBeInstanceOf(SpecificTransportService::class);
});

it('uses Inject before a constructor default', function (): void {
    $service = $this->container->make(AttributePrecedenceTest::class);

    expect($service->transport)->toBeInstanceOf(HttpTransport::class);
});

it('uses Inject before automatic resolution', function (): void {
    $service = $this->container->make(AutowirePrecedenceTest::class);

    expect($service->service)->toBeInstanceOf(InjectedService::class);
});

it('honours a binding for the Inject target', function (): void {
    $this->container->bind(HttpTransport::class, SpecificTransportService::class);

    $service = $this->container->make(AttributePrecedenceTest::class);

    expect($service->transport)->toBeInstanceOf(SpecificTransportService::class);
});

it('uses the default for an optional unbound interface', function (): void {
    $service = $this->container->make(OptionalInterfaceDependencyTest::class);

    expect($service->transport)->toBeNull();
});
