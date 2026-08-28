<?php

declare(strict_types=1);

use mykemeynell\Reflection\Application\Container;
use mykemeynell\Reflection\Exceptions\ContainerException;
use Tests\Fixtures\Attributes\IntersectionValue;
use Tests\Fixtures\Attributes\InvalidIntersectionValueTest;
use Tests\Fixtures\Attributes\InvalidNullValueTest;
use Tests\Fixtures\Attributes\InvalidUnionValueTest;
use Tests\Fixtures\Attributes\InvalidValueTest;
use Tests\Fixtures\Attributes\ReflectionTypeTestHarness;
use Tests\Fixtures\Attributes\UnsupportedReflectionType;
use Tests\Fixtures\Attributes\ValidIntersectionValueTest;
use Tests\Fixtures\Attributes\ValuePrecedenceTest;
use Tests\Fixtures\Attributes\ValueTest;
use Tests\Fixtures\Attributes\ValueTypeMatrixTest;
use Tests\Fixtures\Attributes\ValueTypeTest;

beforeEach(function (): void {
    $this->container = new Container;
});

it('resolves a scalar specified by Value', function (): void {
    $service = $this->container->make(ValueTest::class);

    expect($service->value)->toBe(10);
});

it('prefers Value over a constructor default', function (): void {
    $service = $this->container->make(ValuePrecedenceTest::class);

    expect($service->value)->toBe(10);
});

it('prefers a runtime argument over Value', function (): void {
    $service = $this->container->make(ValuePrecedenceTest::class, value: 30);

    expect($service->value)->toBe(30);
});

it('supports nullable and union Value types', function (): void {
    $service = $this->container->make(ValueTypeTest::class);

    expect($service->nullable)->toBeNull()
        ->and($service->union)->toBe('ten');
});

it('throws when Value does not match the parameter type', function (): void {
    $this->container->make(InvalidValueTest::class);
})->throws(ContainerException::class, 'Value');

it('supports built-in Value types', function (): void {
    $service = $this->container->make(ValueTypeMatrixTest::class);

    expect($service->array)->toBe([1])
        ->and($service->boolean)->toBeTrue()
        ->and(is_callable($service->callable))->toBeTrue()
        ->and($service->false)->toBeFalse()
        ->and($service->float)->toBe(1.0)
        ->and($service->iterable)->toBe([1])
        ->and($service->mixed)->toBe('anything')
        ->and($service->object)->toBeInstanceOf(stdClass::class)
        ->and($service->string)->toBe('ten')
        ->and($service->true)->toBeTrue()
        ->and($service->null)->toBeNull();
});

it('supports an intersection Value type', function (): void {
    $service = $this->container->make(ValidIntersectionValueTest::class);

    expect($service->value)->toBeInstanceOf(IntersectionValue::class);
});

it('rejects incompatible compound Value types', function (string $fixture): void {
    $this->container->make($fixture);
})->with([
    InvalidUnionValueTest::class,
    InvalidIntersectionValueTest::class,
    InvalidNullValueTest::class,
])->throws(ContainerException::class, 'Value');

it('rejects unsupported reflection types', function (): void {
    $reflection = new ReflectionTypeTestHarness;

    expect($reflection->accepts(new UnsupportedReflectionType, 'value'))->toBeFalse();
});
