<?php

declare(strict_types=1);

use mykemeynell\Reflection\Application\Container;
use Tests\Fixtures\Attributes\OverridePrecedenceTest;
use Tests\Fixtures\Container\Contracts\Transport;
use Tests\Fixtures\Container\HttpTransport;
use Tests\Fixtures\Container\SpecificTransportService;

beforeEach(function (): void {
    $this->container = new Container;
});

it('allows Inject to override a contextual binding', function (): void {
    $this->container->when(OverridePrecedenceTest::class)
        ->needs(Transport::class)
        ->give(SpecificTransportService::class);

    $service = $this->container->make(OverridePrecedenceTest::class);

    expect($service->transport)->toBeInstanceOf(HttpTransport::class);
});

it('allows Inject to override a global binding', function (): void {
    $this->container->bind(Transport::class, SpecificTransportService::class);

    $service = $this->container->make(OverridePrecedenceTest::class);

    expect($service->transport)->toBeInstanceOf(HttpTransport::class);
});

it('prefers a runtime argument over Override', function (): void {
    $transport = new SpecificTransportService;

    $service = $this->container->make(OverridePrecedenceTest::class, transport: $transport);

    expect($service->transport)->toBe($transport);
});
