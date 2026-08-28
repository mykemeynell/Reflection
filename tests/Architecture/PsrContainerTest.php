<?php

declare(strict_types=1);

use mykemeynell\Reflection\Application\Container;
use mykemeynell\Reflection\Exceptions\ContainerException;
use mykemeynell\Reflection\Exceptions\DependencyNotSpecifiedException;
use mykemeynell\Reflection\Exceptions\NotFoundException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

arch('container conforms to PSR-11')
    ->expect(Container::class)
    ->toImplement(ContainerInterface::class);

arch('not found exceptions conform to PSR-11')
    ->expect(NotFoundException::class)
    ->toImplement([
        NotFoundExceptionInterface::class,
        ContainerExceptionInterface::class,
    ]);

arch('container exceptions conform to PSR-11')
    ->expect([
        ContainerException::class,
        DependencyNotSpecifiedException::class,
    ])
    ->toImplement(ContainerExceptionInterface::class);

arch('dependency configuration failures are logic exceptions')
    ->expect(DependencyNotSpecifiedException::class)
    ->toExtend(LogicException::class);
