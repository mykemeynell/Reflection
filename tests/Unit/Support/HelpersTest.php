<?php

declare(strict_types=1);

use mykemeynell\Reflection\Application\Container;
use mykemeynell\Reflection\Exceptions\ContainerException;
use mykemeynell\Reflection\Exceptions\NotFoundException;
use Tests\Fixtures\Container\AbstractService;
use Tests\Fixtures\Container\Contracts\Transport;
use Tests\Fixtures\Container\CustomerDispatcher;
use Tests\Fixtures\Container\DirectDispatcher;
use Tests\Fixtures\Container\HttpTransport;
use Tests\Fixtures\Container\ServiceWithDefaultScalar;
use Tests\Fixtures\Container\ServiceWithDependency;
use Tests\Fixtures\Container\ServiceWithMixedParameters;
use Tests\Fixtures\Container\ServiceWithRequiredScalar;
use Tests\Fixtures\Container\SimpleDependency;
use Tests\Fixtures\Container\SimpleService;
use Tests\Fixtures\Container\SpecificTransportService;

use function mykemeynell\Reflection\Helpers\app;

beforeEach(function (): void {
    $this->container = new Container;
});

it('returns the container instance when called without arguments', function (): void {
    expect(app())->toBeInstanceOf(Container::class)
        ->toBe($this->container);
});

it('returns the container instance when called with null', function (): void {
    expect(app(null))->toBeInstanceOf(Container::class)
        ->toBe($this->container);
});

it('returns the same container instance across multiple calls', function (): void {
    $first = app();
    $second = app();

    expect($first)->toBe($second);
});

it('resolves an unbound concrete class', function (): void {
    $service = app(SimpleService::class);

    expect($service)->toBeInstanceOf(SimpleService::class);
});

it('resolves an explicitly bound service', function (): void {
    $this->container->bind(
        Transport::class,
        HttpTransport::class,
    );

    expect(app(Transport::class))
        ->toBeInstanceOf(HttpTransport::class);
});

it('returns the same instance for singleton services', function (): void {
    $this->container->singleton(SimpleService::class);

    $first = app(SimpleService::class);
    $second = app(SimpleService::class);

    expect($first)
        ->toBeInstanceOf(SimpleService::class)
        ->toBe($second);
});

it('resolves a class with named arguments', function (): void {
    $service = app(ServiceWithRequiredScalar::class, timeout: 45);

    expect($service)
        ->toBeInstanceOf(ServiceWithRequiredScalar::class)
        ->and($service->timeout)->toBe(45);
});

it('supports named arguments through the container returned by app', function (): void {
    $service = app()->make(ServiceWithRequiredScalar::class, timeout: 45);

    expect($service->timeout)->toBe(45);
});

it('resolves a class with a parameters array', function (): void {
    $service = app(ServiceWithRequiredScalar::class, ['timeout' => 45]);

    expect($service)
        ->toBeInstanceOf(ServiceWithRequiredScalar::class)
        ->and($service->timeout)->toBe(45);
});

it('overrides default scalar parameters when provided as named arguments', function (): void {
    $service = app(ServiceWithDefaultScalar::class, timeout: 99);

    expect($service->timeout)->toBe(99);
});

it('overrides class dependencies when provided as named arguments', function (): void {
    $dependency = new SimpleDependency;

    $service = app(
        ServiceWithDependency::class,
        dependency: $dependency,
    );

    expect($service->dependency)->toBe($dependency);
});

it('resolves a class with mixed constructor parameters', function (): void {
    $service = app(
        ServiceWithMixedParameters::class,
        timeout: 60,
    );

    expect($service)
        ->toBeInstanceOf(ServiceWithMixedParameters::class)
        ->and($service->dependency)->toBeInstanceOf(SimpleDependency::class)
        ->and($service->timeout)->toBe(60)
        ->and($service->name)->toBe('default');
});

it('allows overriding all mixed constructor parameters', function (): void {
    $dependency = new SimpleDependency;

    $service = app(
        ServiceWithMixedParameters::class,
        dependency: $dependency,
        timeout: 99,
        name: 'custom',
    );

    expect($service->dependency)->toBe($dependency)
        ->and($service->timeout)->toBe(99)
        ->and($service->name)->toBe('custom');
});

it('resolves a closure directly', function (): void {
    $service = app(fn (Container $container): SimpleService => new SimpleService);

    expect($service)->toBeInstanceOf(SimpleService::class);
});

it('passes named arguments to a closure', function (): void {
    $service = app(
        fn (Container $container, array $parameters = []): ServiceWithRequiredScalar => new ServiceWithRequiredScalar(
            $parameters['timeout'],
        ),
        timeout: 60,
    );

    expect($service)
        ->toBeInstanceOf(ServiceWithRequiredScalar::class)
        ->and($service->timeout)->toBe(60);
});

it('passes a parameters array to a closure', function (): void {
    $service = app(
        fn (Container $container, array $parameters = []): ServiceWithRequiredScalar => new ServiceWithRequiredScalar(
            $parameters['timeout'],
        ),
        ['timeout' => 60],
    );

    expect($service)
        ->toBeInstanceOf(ServiceWithRequiredScalar::class)
        ->and($service->timeout)->toBe(60);
});

it('respects contextual bindings when resolving via app', function (): void {
    $this->container->when(DirectDispatcher::class)
        ->needs(Transport::class)
        ->give(HttpTransport::class);

    $this->container->when(CustomerDispatcher::class)
        ->needs(Transport::class)
        ->give(SpecificTransportService::class);

    $direct = app(DirectDispatcher::class);
    $customer = app(CustomerDispatcher::class);

    expect($direct->transport)->toBeInstanceOf(HttpTransport::class)
        ->and($customer->transport)->toBeInstanceOf(SpecificTransportService::class);
});

it('throws not found exception when resolving a non-existent class', function (): void {
    app('Tests\\Fixtures\\Container\\MissingService');
})->throws(
    NotFoundException::class,
    'Cannot resolve [Tests\\Fixtures\\Container\\MissingService]: class does not exist.',
);

it('throws container exception when resolving an uninstantiable abstract class', function (): void {
    app(AbstractService::class);
})->throws(
    ContainerException::class,
    'Cannot instantiate [Tests\\Fixtures\\Container\\AbstractService]',
);

it('throws container exception when a required scalar parameter is missing', function (): void {
    app(ServiceWithRequiredScalar::class);
})->throws(
    ContainerException::class,
    'Cannot resolve parameter [$timeout]',
);
