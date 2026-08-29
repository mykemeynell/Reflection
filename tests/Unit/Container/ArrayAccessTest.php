<?php

declare(strict_types=1);

use mykemeynell\Reflection\Application\Container;
use mykemeynell\Reflection\Exceptions\ContainerException;
use mykemeynell\Reflection\Exceptions\NotFoundException;
use Tests\Fixtures\Container\Configuration;
use Tests\Fixtures\Container\Contracts\Service;
use Tests\Fixtures\Container\Contracts\Transport;
use Tests\Fixtures\Container\HttpTransport;
use Tests\Fixtures\Container\SimpleService;

beforeEach(function (): void {
    $this->container = new Container;
});

it('reports available entries through array access', function (): void {
    $this->container[Transport::class] = HttpTransport::class;
    $this->container[Configuration::class] = new Configuration('test');

    expect(isset($this->container[Transport::class]))->toBeTrue()
        ->and(isset($this->container[Configuration::class]))->toBeTrue()
        ->and(isset($this->container[SimpleService::class]))->toBeTrue()
        ->and(isset($this->container[Service::class]))->toBeFalse()
        ->and(isset($this->container['missing']))->toBeFalse();
});

it('registers class strings as transient bindings', function (): void {
    $this->container[Transport::class] = HttpTransport::class;

    $first = $this->container->offsetGet(Transport::class);
    $second = $this->container->offsetGet(Transport::class);

    expect($first)->toBeInstanceOf(HttpTransport::class)
        ->and($second)->toBeInstanceOf(HttpTransport::class)
        ->not->toBe($first);
});

it('registers closures as factory bindings', function (): void {
    $expected = new HttpTransport;

    $this->container[Transport::class] = fn (): Transport => $expected;

    expect($this->container[Transport::class])->toBe($expected);
});

it('registers objects as instances', function (): void {
    $expected = new HttpTransport;

    $this->container[Transport::class] = $expected;

    expect($this->container[Transport::class])->toBe($expected);
});

it('registers null as a self binding', function (): void {
    $this->container[SimpleService::class] = null;

    expect($this->container->offsetGet(SimpleService::class))
        ->toBeInstanceOf(SimpleService::class);
});

it('removes bindings through array access', function (): void {
    $this->container[Transport::class] = HttpTransport::class;

    unset($this->container[Transport::class]);

    expect(isset($this->container[Transport::class]))->toBeFalse()
        ->and(fn (): mixed => $this->container[Transport::class])
        ->toThrow(NotFoundException::class);
});

it('removes instances through array access', function (): void {
    $this->container[Transport::class] = new HttpTransport;

    unset($this->container[Transport::class]);

    expect(isset($this->container[Transport::class]))->toBeFalse()
        ->and(fn (): mixed => $this->container[Transport::class])
        ->toThrow(NotFoundException::class);
});

it('ignores removal of an unknown entry', function (): void {
    unset($this->container['missing']);

    expect(isset($this->container['missing']))->toBeFalse();
});

it('throws when reading an unknown entry', function (): void {
    expect(fn (): mixed => $this->container['missing'])
        ->toThrow(NotFoundException::class);
});

it('throws when an array binding cannot be constructed', function (): void {
    $this->container['broken'] = 'Tests\\Fixtures\\Container\\MissingClass';

    expect(fn (): mixed => $this->container['broken'])
        ->toThrow(ContainerException::class);
});

it('rejects non-string offsets', function (Closure $operation): void {
    expect(fn (): mixed => $operation($this->container))
        ->toThrow(TypeError::class);
})->with([
    'exists' => fn (Container $container): bool => $container->offsetExists(1),
    'get' => fn (Container $container): mixed => $container->offsetGet(1),
    'set' => fn (Container $container) => $container->offsetSet(1, HttpTransport::class),
    'unset' => fn (Container $container) => $container->offsetUnset(1),
]);

it('rejects unsupported values without registering them', function (mixed $value): void {
    expect(function () use ($value): void {
        $this->container[Transport::class] = $value;
    })->toThrow(TypeError::class)
        ->and(isset($this->container[Transport::class]))->toBeFalse();

})->with([
    'array' => [[]],
    'integer' => [1],
    'boolean' => [true],
    'float' => [1.5],
]);
