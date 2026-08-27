<?php

declare(strict_types=1);

use mykemeynell\Reflection\Application\Container;
use mykemeynell\Reflection\Bindings\ContextualBindingBuilder;
use mykemeynell\Reflection\Exceptions\ContainerException;
use mykemeynell\Reflection\Exceptions\DependencyNotSpecifiedException;
use mykemeynell\Reflection\Exceptions\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Tests\Fixtures\Container\AbstractService;
use Tests\Fixtures\Container\Circular\CircularA;
use Tests\Fixtures\Container\Contracts\Transport;
use Tests\Fixtures\Container\DirectDispatcher;
use Tests\Fixtures\Container\HttpTransport;
use Tests\Fixtures\Container\ServiceWithRequiredScalar;
use Tests\Fixtures\Container\SimpleService;

beforeEach(function (): void {
    $this->container = new Container;
});

it('implements the PSR container interface', function (): void {
    expect($this->container)
        ->toBeInstanceOf(ContainerInterface::class);
});

it('resolves services through get', function (): void {
    $this->container->bind(
        Transport::class,
        HttpTransport::class,
    );

    expect($this->container->get(Transport::class))
        ->toBeInstanceOf(HttpTransport::class);
});

it('reports explicitly bound services as available', function (): void {
    $this->container->bind(
        Transport::class,
        HttpTransport::class,
    );

    expect($this->container->has(Transport::class))
        ->toBeTrue();
});

it('reports registered instances as available', function (): void {
    $this->container->instance(
        Transport::class,
        new HttpTransport,
    );

    expect($this->container->has(Transport::class))
        ->toBeTrue();
});

it('reports registered singletons as available', function (): void {
    $this->container->singleton(
        Transport::class,
        HttpTransport::class,
    );

    expect($this->container->has(Transport::class))
        ->toBeTrue();
});

it('reports automatically resolvable concrete classes as available', function (): void {
    expect($this->container->has(SimpleService::class))
        ->toBeTrue();
});

it('reports unknown services as unavailable', function (): void {
    expect(
        $this->container->has(
            'Tests\\Fixtures\\Container\\DoesNotExist',
        ),
    )->toBeFalse();
});

it('reports unbound interfaces as unavailable', function (): void {
    expect($this->container->has(Transport::class))
        ->toBeFalse();
});

it('throws a not found exception implementing PSR interfaces when get cannot locate an entry', function (): void {
    try {
        $this->container->get('Tests\\Fixtures\\Container\\DoesNotExist');
        $this->fail('Expected NotFoundException was not thrown.');
    } catch (NotFoundException $e) {
        expect($e)
            ->toBeInstanceOf(NotFoundExceptionInterface::class)
            ->toBeInstanceOf(ContainerExceptionInterface::class);
    }
});

it('throws a not found exception when get is called on an unbound interface', function (): void {
    try {
        $this->container->get(Transport::class);
        $this->fail('Expected NotFoundException was not thrown.');
    } catch (NotFoundException $e) {
        expect($e)
            ->toBeInstanceOf(NotFoundExceptionInterface::class)
            ->toBeInstanceOf(ContainerExceptionInterface::class);
    }
});

it('throws a container exception implementing PSR interface when get cannot construct an entry due to scalar parameters', function (): void {
    expect($this->container->has(ServiceWithRequiredScalar::class))->toBeTrue();

    try {
        $this->container->get(ServiceWithRequiredScalar::class);
        $this->fail('Expected ContainerException was not thrown.');
    } catch (ContainerException $e) {
        expect($e)
            ->toBeInstanceOf(ContainerExceptionInterface::class)
            ->not->toBeInstanceOf(NotFoundExceptionInterface::class);
    }
});

it('throws a container exception and not a not found exception when a dependency cannot be resolved', function (): void {
    expect($this->container->has(DirectDispatcher::class))->toBeTrue();

    try {
        $this->container->get(DirectDispatcher::class);
        $this->fail('Expected ContainerException was not thrown.');
    } catch (ContainerException $e) {
        expect($e)
            ->toBeInstanceOf(ContainerExceptionInterface::class)
            ->not->toBeInstanceOf(NotFoundExceptionInterface::class);
    }
});

it('throws a container exception when get is called on an uninstantiable abstract class', function (): void {
    expect($this->container->has(AbstractService::class))->toBeTrue();

    try {
        $this->container->get(AbstractService::class);
        $this->fail('Expected ContainerException was not thrown.');
    } catch (ContainerException $e) {
        expect($e)
            ->toBeInstanceOf(ContainerExceptionInterface::class)
            ->not->toBeInstanceOf(NotFoundExceptionInterface::class);
    }
});

it('throws a container exception when get is called on an entry bound to a missing class', function (): void {
    $this->container->bind('broken_service', 'Tests\\Fixtures\\Container\\MissingClass');

    expect($this->container->has('broken_service'))->toBeTrue();

    try {
        $this->container->get('broken_service');
        $this->fail('Expected ContainerException was not thrown.');
    } catch (ContainerException $e) {
        expect($e)
            ->toBeInstanceOf(ContainerExceptionInterface::class)
            ->not->toBeInstanceOf(NotFoundExceptionInterface::class);
    }
});

it('throws a container exception when a bound factory throws an exception', function (): void {
    $this->container->bind('failing_service', function (): never {
        throw new RuntimeException('Factory error');
    });

    expect($this->container->has('failing_service'))->toBeTrue();

    try {
        $this->container->get('failing_service');
        $this->fail('Expected ContainerException was not thrown.');
    } catch (ContainerException $e) {
        expect($e)
            ->toBeInstanceOf(ContainerExceptionInterface::class)
            ->not->toBeInstanceOf(NotFoundExceptionInterface::class);
    }
});

it('throws a container exception when circular dependency is detected during get', function (): void {
    expect($this->container->has(CircularA::class))->toBeTrue();

    try {
        $this->container->get(CircularA::class);
        $this->fail('Expected ContainerException was not thrown.');
    } catch (ContainerException $e) {
        expect($e)
            ->toBeInstanceOf(ContainerExceptionInterface::class)
            ->not->toBeInstanceOf(NotFoundExceptionInterface::class);
    }
});

it('throws a container exception when give is called before needs in contextual binding', function (): void {
    $builder = new ContextualBindingBuilder($this->container, [DirectDispatcher::class]);

    try {
        $builder->give(HttpTransport::class);
        $this->fail('Expected DependencyNotSpecifiedException was not thrown.');
    } catch (DependencyNotSpecifiedException $e) {
        expect($e)
            ->toBeInstanceOf(ContainerExceptionInterface::class)
            ->toBeInstanceOf(LogicException::class);
    }
});
