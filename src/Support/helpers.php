<?php

declare(strict_types=1);

namespace mykemeynell\Reflector\Helpers;

use mykemeynell\Reflector\Application\Container;

if (! function_exists('app')) {
    /**
     * Resolve and return a service from the container or return the container instance itself.
     *
     * @param  string|\Closure|null  $abstract  The abstract type or closure to resolve. If null, the container instance is returned.
     * @param  mixed  ...$parameters  Additional parameters to pass to the resolution process.
     * @return mixed The resolved service instance or the container instance.
     */
    function app(string|\Closure|null $abstract = null, ...$parameters): mixed
    {
        return ($container = Container::getInstance()) && $abstract !== null
            ? $container->make($abstract, $parameters)
            : $container;
    }
}
