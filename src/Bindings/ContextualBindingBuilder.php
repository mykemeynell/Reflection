<?php

declare(strict_types=1);

namespace mykemeynell\Reflection\Bindings;

use Closure;
use mykemeynell\Reflection\Application\Container;
use mykemeynell\Reflection\Exceptions\DependencyNotSpecifiedException;

final class ContextualBindingBuilder
{
    private ?string $dependency = null;

    public function __construct(
        private readonly Container $container,
        private readonly array $consumers
    ) {}

    /**
     * Specifies a dependency that should be used in a contextual binding.
     *
     * @param  string  $dependency  The name of the dependency to be associated with a contextual implementation.
     */
    public function needs(string $dependency): self
    {
        $this->dependency = $dependency;

        return $this;
    }

    /**
     * Binds a given implementation to a contextual dependency for specified consumers.
     *
     * @param  Closure|string|object  $implementation  The implementation to associate with the dependency.
     *                                                 This can be a concrete class name as a string, a Closure, or an object instance.
     *
     * @throws DependencyNotSpecifiedException If no contextual dependency has been specified before calling this method.
     */
    public function give(string|object $implementation): void
    {
        if ($this->dependency === null) {
            throw new DependencyNotSpecifiedException('A contextual dependency must be specified before using needs().');
        }

        $this->container->addContextualBinding(
            $this->consumers,
            $this->dependency,
            $implementation
        );
    }
}
