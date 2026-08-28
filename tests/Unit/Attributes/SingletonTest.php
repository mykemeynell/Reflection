<?php

declare(strict_types=1);

use mykemeynell\Reflection\Application\Container;
use Tests\Fixtures\Attributes\ServiceWithSingletonDependency;
use Tests\Fixtures\Attributes\SingletonPrecedenceTest;

beforeEach(function (): void {
    $this->container = new Container;
});

it('shares a class marked with Singleton', function (): void {
    $first = $this->container->make(SingletonPrecedenceTest::class);
    $second = $this->container->make(SingletonPrecedenceTest::class);

    expect($first)->toBe($second);
});

it('shares Singleton classes resolved as dependencies', function (): void {
    $first = $this->container->make(ServiceWithSingletonDependency::class);
    $second = $this->container->make(ServiceWithSingletonDependency::class);

    expect($first->dependency)->toBe($second->dependency);
});

it('allows an explicit binding to override Singleton', function (): void {
    $this->container->bind(SingletonPrecedenceTest::class);

    $first = $this->container->make(SingletonPrecedenceTest::class);
    $second = $this->container->make(SingletonPrecedenceTest::class);

    expect($first)->not->toBe($second);
});

it('prefers a registered instance over Singleton', function (): void {
    $instance = new SingletonPrecedenceTest;
    $this->container->instance(SingletonPrecedenceTest::class, $instance);

    expect($this->container->make(SingletonPrecedenceTest::class))->toBe($instance);
});
