<?php

declare(strict_types=1);

use mykemeynell\Reflector\Application\Container;
use Tests\Fixtures\Container\ChangeProcessor;
use Tests\Fixtures\Container\Contracts\Transport;
use Tests\Fixtures\Container\CustomerDispatcher;
use Tests\Fixtures\Container\DirectDispatcher;
use Tests\Fixtures\Container\HttpTransport;
use Tests\Fixtures\Container\SpecificServiceDispatcher;
use Tests\Fixtures\Container\SpecificTransportService;
use Tests\Fixtures\Container\OutletDispatcher;

beforeEach(function (): void {
    $this->container = new Container;
});

it('uses a contextual binding for the specified consumer', function (): void {
    $this->container
        ->when(SpecificServiceDispatcher::class)
        ->needs(Transport::class)
        ->give(SpecificTransportService::class);

    $dispatcher = $this->container->make(
        SpecificServiceDispatcher::class,
    );

    expect($dispatcher->transport)
        ->toBeInstanceOf(SpecificTransportService::class);
});

it('prefers a contextual binding over a global binding', function (): void {
    $this->container->bind(
        Transport::class,
        HttpTransport::class,
    );

    $this->container
        ->when(SpecificServiceDispatcher::class)
        ->needs(Transport::class)
        ->give(SpecificTransportService::class);

    $dispatcher = $this->container->make(
        SpecificServiceDispatcher::class,
    );

    expect($dispatcher->transport)
        ->toBeInstanceOf(SpecificTransportService::class);
});

it('does not leak a contextual binding to another consumer', function (): void {
    $this->container->bind(
        Transport::class,
        HttpTransport::class,
    );

    $this->container
        ->when(SpecificServiceDispatcher::class)
        ->needs(Transport::class)
        ->give(SpecificTransportService::class);

    $direct = $this->container->make(
        DirectDispatcher::class,
    );

    expect($direct->transport)
        ->toBeInstanceOf(HttpTransport::class);
});

it('allows different consumers to receive different implementations of the same dependency', function (): void {
    $this->container
        ->when(SpecificServiceDispatcher::class)
        ->needs(Transport::class)
        ->give(SpecificTransportService::class);

    $this->container
        ->when(DirectDispatcher::class)
        ->needs(Transport::class)
        ->give(HttpTransport::class);

    $muleSoft = $this->container->make(
        SpecificServiceDispatcher::class,
    );

    $direct = $this->container->make(
        DirectDispatcher::class,
    );

    expect($muleSoft->transport)
        ->toBeInstanceOf(SpecificTransportService::class)
        ->and($direct->transport)
        ->toBeInstanceOf(HttpTransport::class);
});

it('applies one contextual binding to multiple consumers', function (): void {
    $this->container
        ->when(
            CustomerDispatcher::class,
            OutletDispatcher::class,
        )
        ->needs(Transport::class)
        ->give(SpecificTransportService::class);

    $customer = $this->container->make(
        CustomerDispatcher::class,
    );

    $outlet = $this->container->make(
        OutletDispatcher::class,
    );

    expect($customer->transport)
        ->toBeInstanceOf(SpecificTransportService::class)
        ->and($outlet->transport)
        ->toBeInstanceOf(SpecificTransportService::class);
});

it('resolves a contextual binding from a closure', function (): void {
    $expected = new SpecificTransportService;

    $this->container
        ->when(SpecificServiceDispatcher::class)
        ->needs(Transport::class)
        ->give(
            fn (): Transport => $expected,
        );

    $dispatcher = $this->container->make(
        SpecificServiceDispatcher::class,
    );

    expect($dispatcher->transport)->toBe($expected);
});

it('supports an object instance as a contextual implementation', function (): void {
    $expected = new SpecificTransportService;

    $this->container
        ->when(SpecificServiceDispatcher::class)
        ->needs(Transport::class)
        ->give(fn () => $expected);

    $dispatcher = $this->container->make(
        SpecificServiceDispatcher::class,
    );

    expect($dispatcher->transport)->toBe($expected);
});

it('applies contextual bindings during nested dependency resolution', function (): void {
    $this->container
        ->when(SpecificServiceDispatcher::class)
        ->needs(Transport::class)
        ->give(SpecificTransportService::class);

    $processor = $this->container->make(
        ChangeProcessor::class,
    );

    expect($processor->dispatcher)
        ->toBeInstanceOf(SpecificServiceDispatcher::class)
        ->and($processor->dispatcher->transport)
        ->toBeInstanceOf(SpecificTransportService::class);
});

it('falls back to the global binding when the consumer has no contextual binding', function (): void {
    $this->container->bind(
        Transport::class,
        HttpTransport::class,
    );

    $dispatcher = $this->container->make(
        DirectDispatcher::class,
    );

    expect($dispatcher->transport)
        ->toBeInstanceOf(HttpTransport::class);
});

it('allows an existing contextual binding to be replaced', function (): void {
    $this->container
        ->when(SpecificServiceDispatcher::class)
        ->needs(Transport::class)
        ->give(HttpTransport::class);

    $this->container
        ->when(SpecificServiceDispatcher::class)
        ->needs(Transport::class)
        ->give(SpecificTransportService::class);

    $dispatcher = $this->container->make(
        SpecificServiceDispatcher::class,
    );

    expect($dispatcher->transport)
        ->toBeInstanceOf(SpecificTransportService::class);
});
