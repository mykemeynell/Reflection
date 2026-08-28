<?php

declare(strict_types=1);

namespace mykemeynell\Reflection\Concerns;

trait NormalizesParameters
{
    /**
     * Normalizes positional and legacy named parameter arrays into runtime overrides.
     *
     * @param  array<array-key, mixed>  $parameters
     * @return array<array-key, mixed>
     */
    private function normalizeParameters(array $parameters): array
    {
        if (count($parameters) === 1 && array_key_exists(0, $parameters) && is_array($parameters[0])) {
            return $parameters[0];
        }

        if (count($parameters) === 1 && array_key_exists('parameters', $parameters) && is_array($parameters['parameters'])) {
            return $parameters['parameters'];
        }

        return $parameters;
    }
}
