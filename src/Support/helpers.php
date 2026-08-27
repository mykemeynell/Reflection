<?php

declare(strict_types=1);

namespace mykemeynell\Reflector\Helpers;

use Closure;
use mykemeynell\Reflector\Application\Container;

if (! function_exists('app')) {
    /**
     * Resolve and return a service from the container or return the container instance itself.
     *
     * @param  string|Closure|null  $abstract  The abstract type or closure to resolve. If null, the container instance is returned.
     * @param  mixed  ...$parameters  Additional parameters to pass to the resolution process.
     * @return mixed The resolved service instance or the container instance.
     */
    function app(string|Closure|null $abstract = null, ...$parameters): mixed
    {
        if (count($parameters) === 1 && isset($parameters[0]) && is_array($parameters[0])) {
            $parameters = $parameters[0];
        }

        return ($container = Container::getInstance()) && $abstract !== null
            ? $container->make($abstract, $parameters)
            : $container;
    }
}
