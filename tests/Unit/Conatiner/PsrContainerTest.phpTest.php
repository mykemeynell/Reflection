<?php

declare(strict_types=1);

use mykemeynell\Reflector\Application\Container;
use mykemeynell\Reflector\Exceptions\ContainerException;
use mykemeynell\Reflector\Exceptions\NotFoundException;
use Psr\Container\ContainerInterface;
use Tests\Fixtures\Container\Contracts\Transport;
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

it('throws a not found exception when get cannot locate an entry', function (): void {
    $this->container->get(
    'Tests\\Fixtures\\Container\\DoesNotExist',
    );
})->throws(NotFoundException::class);

it('throws a container exception when get cannot construct an entry', function (): void {
    $this->container->get(
        ServiceWithRequiredScalar::class,
    );
})->throws(ContainerException::class);
