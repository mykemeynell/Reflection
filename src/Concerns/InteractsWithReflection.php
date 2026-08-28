<?php

declare(strict_types=1);

namespace mykemeynell\Reflection\Concerns;

use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;

trait InteractsWithReflection
{
    /**
     * Checks if the given value is acceptable for the specified parameter.
     *
     * @param  ReflectionParameter  $parameter  The parameter to check against.
     * @param  mixed  $value  The value to be checked for compatibility with the parameter.
     * @return bool Returns true if the value is acceptable for the parameter, otherwise false.
     */
    private function parameterAcceptsValue(ReflectionParameter $parameter, mixed $value): bool
    {
        $type = $parameter->getType();

        return $type === null || $this->typeAcceptsValue($type, $value);
    }

    /**
     * Retrieves the type name of the specified parameter.
     *
     * @param  ReflectionParameter  $parameter  The parameter whose type name is to be determined.
     * @return string Returns the name of the parameter's type, or 'mixed' if no type is specified.
     */
    private function parameterTypeName(ReflectionParameter $parameter): string
    {
        return (string) ($parameter->getType() ?? 'mixed');
    }

    /**
     * Determines if the given value is compatible with the specified type.
     *
     * @param  ReflectionType  $type  The type to check compatibility against.
     * @param  mixed  $value  The value to be checked for compatibility with the type.
     * @return bool Returns true if the value is compatible with the specified type, otherwise false.
     */
    private function typeAcceptsValue(ReflectionType $type, mixed $value): bool
    {
        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $unionType) {
                if ($this->typeAcceptsValue($unionType, $value)) {
                    return true;
                }
            }

            return false;
        }

        if ($type instanceof ReflectionIntersectionType) {
            foreach ($type->getTypes() as $intersectionType) {
                if (! $this->typeAcceptsValue($intersectionType, $value)) {
                    return false;
                }
            }

            return true;
        }

        if (! $type instanceof ReflectionNamedType) {
            return false;
        }

        return $this->namedTypeAcceptsValue($type, $value);
    }

    /**
     * Determines if the given value is compatible with the specified named type.
     *
     * @param  ReflectionNamedType  $type  The named type to validate against.
     * @param  mixed  $value  The value to check for compatibility with the named type.
     * @return bool Returns true if the value matches the named type, otherwise false.
     */
    private function namedTypeAcceptsValue(ReflectionNamedType $type, mixed $value): bool
    {
        if ($value === null) {
            return $type->allowsNull();
        }

        $name = $type->getName();

        if (! $type->isBuiltin()) {
            return $value instanceof $name;
        }

        return match ($name) {
            'array' => is_array($value),
            'bool' => is_bool($value),
            'callable' => is_callable($value),
            'false' => $value === false,
            'float' => is_float($value) || is_int($value),
            'int' => is_int($value),
            'iterable' => is_iterable($value),
            'mixed' => true,
            'null' => false,
            'object' => is_object($value),
            'string' => is_string($value),
            'true' => $value === true,
            default => false,
        };
    }
}
