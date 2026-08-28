<?php

declare(strict_types=1);

use mykemeynell\Reflection\Application\Container;
use mykemeynell\Reflection\Exceptions\ContainerException;
use Tests\Fixtures\Container\AbstractService;
use Tests\Fixtures\Container\Circular\CircularA;
use Tests\Fixtures\Container\Configuration;
use Tests\Fixtures\Container\ConfiguredTransport;
use Tests\Fixtures\Container\Contracts\Transport;
use Tests\Fixtures\Container\ServiceWithDefaultScalar;
use Tests\Fixtures\Container\ServiceWithDependency;
use Tests\Fixtures\Container\ServiceWithMixedParameters;
use Tests\Fixtures\Container\ServiceWithRequiredScalar;
use Tests\Fixtures\Container\SimpleDependency;
use Tests\Fixtures\Container\SimpleService;

beforeEach(function (): void {
    $this->container = new Container;
});

it('automatically resolves an unbound concrete class', function (): void {
    expect($this->container->make(SimpleService::class))
        ->toBeInstanceOf(SimpleService::class);
});

it('creates a new instance when automatically resolving a concrete class', function (): void {
    $first = $this->container->make(SimpleService::class);
    $second = $this->container->make(SimpleService::class);

    expect($first)->not->toBe($second);
});

it('automatically resolves constructor dependencies', function (): void {
    $service = $this->container->make(
        ServiceWithDependency::class,
    );

    expect($service)
        ->toBeInstanceOf(ServiceWithDependency::class)
        ->and($service->dependency)
        ->toBeInstanceOf(SimpleDependency::class);
});

it('uses a registered dependency while automatically constructing a consumer', function (): void {
    $dependency = new SimpleDependency;

    $this->container->instance(
        SimpleDependency::class,
        $dependency,
    );

    $service = $this->container->make(
        ServiceWithDependency::class,
    );

    expect($service->dependency)->toBe($dependency);
});

it('uses the default value of an unresolved scalar parameter', function (): void {
    $service = $this->container->make(
        ServiceWithDefaultScalar::class,
    );

    expect($service->timeout)->toBe(30);
});

it('throws when a required scalar constructor parameter cannot be resolved', function (): void {
    $this->container->make(
        ServiceWithRequiredScalar::class,
    );
})->throws(
    ContainerException::class,
    'Cannot resolve parameter [$timeout]',
);

it('resolves a class with required scalar parameters provided as named parameters', function (): void {
    $service = $this->container->make(
        ServiceWithRequiredScalar::class,
        ['timeout' => 45],
    );

    expect($service)
        ->toBeInstanceOf(ServiceWithRequiredScalar::class)
        ->and($service->timeout)->toBe(45);
});

it('accepts named arguments directly', function (): void {
    $service = $this->container->make(
        ServiceWithMixedParameters::class,
        timeout: 45,
        name: 'named',
    );

    expect($service->timeout)->toBe(45)
        ->and($service->name)->toBe('named');
});

it('accepts the legacy parameter array as a named argument', function (): void {
    $service = $this->container->make(
        ServiceWithMixedParameters::class,
        parameters: ['timeout' => 45, 'name' => 'legacy'],
    );

    expect($service->timeout)->toBe(45)
        ->and($service->name)->toBe('legacy');
});

it('passes named arguments to a closure', function (): void {
    $service = $this->container->make(
        fn (Container $container, array $parameters): ServiceWithRequiredScalar => new ServiceWithRequiredScalar(
            $parameters['timeout'],
        ),
        timeout: 75,
    );

    expect($service->timeout)->toBe(75);
});

it('overrides default scalar parameter when provided as a named parameter', function (): void {
    $service = $this->container->make(
        ServiceWithDefaultScalar::class,
        ['timeout' => 99],
    );

    expect($service->timeout)->toBe(99);
});

it('overrides class dependency when provided as a named parameter', function (): void {
    $dependency = new SimpleDependency;

    $service = $this->container->make(
        ServiceWithDependency::class,
        ['dependency' => $dependency],
    );

    expect($service->dependency)->toBe($dependency);
});

it('passes named parameters to a bound closure during resolution', function (): void {
    $this->container->bind(
        'custom_service',
        fn (Container $container, array $parameters = []): ServiceWithRequiredScalar => new ServiceWithRequiredScalar(
            $parameters['timeout'],
        ),
    );

    $service = $this->container->make('custom_service', ['timeout' => 120]);

    expect($service)
        ->toBeInstanceOf(ServiceWithRequiredScalar::class)
        ->and($service->timeout)->toBe(120);
});

it('passes named parameters when resolving an abstract bound to a concrete class', function (): void {
    $this->container->bind(
        Transport::class,
        ConfiguredTransport::class,
    );

    $configuration = new Configuration('custom');

    $transport = $this->container->make(
        Transport::class,
        ['configuration' => $configuration],
    );

    expect($transport)
        ->toBeInstanceOf(ConfiguredTransport::class)
        ->and($transport->configuration)->toBe($configuration);
});

it('resolves a class with mixed constructor parameters', function (): void {
    $service = $this->container->make(
        ServiceWithMixedParameters::class,
        ['timeout' => 60],
    );

    expect($service)
        ->toBeInstanceOf(ServiceWithMixedParameters::class)
        ->and($service->dependency)->toBeInstanceOf(SimpleDependency::class)
        ->and($service->timeout)->toBe(60)
        ->and($service->name)->toBe('default');
});

it('allows overriding all mixed constructor parameters', function (): void {
    $dependency = new SimpleDependency;

    $service = $this->container->make(
        ServiceWithMixedParameters::class,
        [
            'dependency' => $dependency,
            'timeout' => 99,
            'name' => 'custom',
        ],
    );

    expect($service->dependency)->toBe($dependency)
        ->and($service->timeout)->toBe(99)
        ->and($service->name)->toBe('custom');
});

it('caches singleton instance initialized with named parameters', function (): void {
    $this->container->singleton(
        ServiceWithRequiredScalar::class,
    );

    $first = $this->container->make(
        ServiceWithRequiredScalar::class,
        ['timeout' => 50],
    );

    $second = $this->container->make(
        ServiceWithRequiredScalar::class,
        ['timeout' => 100],
    );

    expect($first)
        ->toBe($second)
        ->and($second->timeout)->toBe(50);
});

it('throws when attempting to resolve a class that does not exist', function (): void {
    $this->container->make(
        'Tests\\Fixtures\\Container\\MissingService',
    );
})->throws(
    RuntimeException::class,
    'class does not exist',
);

it('throws when attempting to instantiate an abstract class', function (): void {
    $this->container->make(AbstractService::class);
})->throws(
    RuntimeException::class,
    'Cannot instantiate',
);

it('detects a direct circular dependency in the object graph', function (): void {
    $this->container->make(CircularA::class);
})->throws(
    RuntimeException::class,
    'Circular dependency detected',
);

it('includes the dependency chain when reporting a circular dependency', function (): void {
    try {
        $this->container->make(CircularA::class);
    } catch (RuntimeException $exception) {
        expect($exception->getMessage())
            ->toContain(CircularA::class)
            ->toContain('CircularB')
            ->toContain('->');

        return;
    }

    $this->fail('Expected a circular dependency exception.');
});
