<?php

declare(strict_types=1);

namespace mykemeynell\Reflection\Concerns;

use Closure;
use mykemeynell\Reflection\Attributes\Inject;
use mykemeynell\Reflection\Attributes\Override;
use mykemeynell\Reflection\Attributes\Value;
use mykemeynell\Reflection\Exceptions\ContainerException;
use Psr\Container\NotFoundExceptionInterface;
use ReflectionClass;
use ReflectionParameter;
use ReflectionProperty;

trait InteractsWithAttributes
{
    abstract public function make(string|Closure $abstract, mixed ...$parameters): mixed;

    /**
     * @template TAttribute of object
     *
     * @param  class-string<TAttribute>  $attribute
     * @return TAttribute|null
     */
    private function attributeInstance(
        ReflectionClass|ReflectionParameter|ReflectionProperty $reflection,
        string $attribute,
    ): ?object {
        $attributes = $reflection->getAttributes($attribute);

        if ($attributes === []) {
            return null;
        }

        return $attributes[0]->newInstance();
    }

    private function validateParameterAttributes(
        string $consumer,
        ReflectionParameter $parameter,
        ?Inject $inject,
        ?Override $override,
        ?Value $value,
    ): void {
        if ($override !== null && $inject === null) {
            throw new ContainerException(
                sprintf(
                    'Override requires Inject on parameter [$%s] while building [%s].',
                    $parameter->getName(),
                    $consumer,
                )
            );
        }

        if ($inject !== null && $value !== null) {
            throw new ContainerException(
                sprintf(
                    'Inject and Value cannot be combined on parameter [$%s] while building [%s].',
                    $parameter->getName(),
                    $consumer,
                )
            );
        }
    }

    private function resolveInjectedParameter(
        string $consumer,
        ReflectionParameter $parameter,
        Inject $inject,
    ): mixed {
        try {
            return $this->make($inject->abstract);
        } catch (NotFoundExceptionInterface $exception) {
            throw new ContainerException(
                sprintf(
                    'Inject target [%s] for parameter [$%s] while building [%s] could not be resolved.',
                    $inject->abstract,
                    $parameter->getName(),
                    $consumer,
                ),
                previous: $exception,
            );
        }
    }
}
