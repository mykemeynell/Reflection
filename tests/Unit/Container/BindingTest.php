<?php

declare(strict_types=1);

use mykemeynell\Reflection\Application\Container;
use Tests\Fixtures\Container\Configuration;
use Tests\Fixtures\Container\ConfiguredTransport;
use Tests\Fixtures\Container\Contracts\Transport;
use Tests\Fixtures\Container\HttpTransport;
use Tests\Fixtures\Container\SimpleService;
use Tests\Fixtures\Container\SpecificTransportService;

beforeEach(function (): void {
    $this->container = new Container;
});

it('resolves an explicitly bound implementation', function (): void {
    $this->container->bind(
        Transport::class,
        HttpTransport::class,
    );

    expect($this->container->make(Transport::class))
        ->toBeInstanceOf(HttpTransport::class);
});

it('defaults a binding implementation to the abstract itself', function (): void {
    $this->container->bind(SimpleService::class);

    expect($this->container->make(SimpleService::class))
        ->toBeInstanceOf(SimpleService::class);
});

it('creates a new instance for each normal binding resolution', function (): void {
    $this->container->bind(
        Transport::class,
        HttpTransport::class,
    );

    $first = $this->container->make(Transport::class);
    $second = $this->container->make(Transport::class);

    expect($first)
        ->toBeInstanceOf(HttpTransport::class)
        ->not->toBe($second);
});

it('resolves a binding from a closure', function (): void {
    $expected = new HttpTransport;

    $this->container->bind(
        Transport::class,
        fn (): Transport => $expected,
    );

    expect($this->container->make(Transport::class))
        ->toBe($expected);
});

it('passes the container to binding closures', function (): void {
    $configuration = new Configuration('test');

    $this->container->instance(
        Configuration::class,
        $configuration,
    );

    $this->container->bind(
        Transport::class,
        fn (Container $container): Transport => new ConfiguredTransport(
            $container->make(Configuration::class),
        ),
    );

    $transport = $this->container->make(Transport::class);

    expect($transport)
        ->toBeInstanceOf(ConfiguredTransport::class)
        ->and($transport->configuration)->toBe($configuration);
});

it('returns a registered instance unchanged', function (): void {
    $transport = new HttpTransport;

    $this->container->instance(
        Transport::class,
        $transport,
    );

    expect($this->container->make(Transport::class))
        ->toBe($transport);
});

it('returns the same object from a singleton binding', function (): void {
    $this->container->singleton(
        Transport::class,
        HttpTransport::class,
    );

    $first = $this->container->make(Transport::class);
    $second = $this->container->make(Transport::class);

    expect($first)
        ->toBeInstanceOf(HttpTransport::class)
        ->toBe($second);
});

it('allows a concrete class to be registered as a singleton', function (): void {
    $this->container->singleton(SimpleService::class);

    $first = $this->container->make(SimpleService::class);
    $second = $this->container->make(SimpleService::class);

    expect($first)->toBe($second);
});

it('resolves singleton closures only once', function (): void {
    $resolutions = 0;

    $this->container->singleton(
        Transport::class,
        function () use (&$resolutions): Transport {
            $resolutions++;

            return new HttpTransport;
        },
    );

    $first = $this->container->make(Transport::class);
    $second = $this->container->make(Transport::class);

    expect($first)
        ->toBe($second)
        ->and($resolutions)->toBe(1);
});

it('allows an existing binding to be replaced', function (): void {
    $this->container->bind(
        Transport::class,
        HttpTransport::class,
    );

    $this->container->bind(
        Transport::class,
        ConfiguredTransport::class,
    );

    $this->container->instance(
        Configuration::class,
        new Configuration('replacement'),
    );

    expect($this->container->make(Transport::class))
        ->toBeInstanceOf(ConfiguredTransport::class);
});

it('forgets a resolved singleton when the service is rebound', function (): void {
    $this->container->singleton(
        Transport::class,
        HttpTransport::class,
    );

    expect($this->container->make(Transport::class))
        ->toBeInstanceOf(HttpTransport::class);

    $this->container->bind(
        Transport::class,
        SpecificTransportService::class,
    );

    expect($this->container->make(Transport::class))
        ->toBeInstanceOf(SpecificTransportService::class);
});
