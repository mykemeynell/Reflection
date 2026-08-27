<?php

declare(strict_types=1);

namespace mykemeynell\Reflector\Bindings;

use Closure;
use LogicException;
use mykemeynell\Reflector\Application\Container;

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
     * @param string $dependency The name of the dependency to be associated with a contextual implementation.
     * @return self
     */
    public function needs(string $dependency): self
    {
        $this->dependency = $dependency;

        return $this;
    }

    /**
     * Binds a given implementation to a contextual dependency for specified consumers.
     *
     * @param string|Closure $implementation The implementation to associate with the dependency.
     *                                        This can be a concrete class name as a string or a Closure.
     * @return void
     * @throws LogicException If no contextual dependency has been specified before calling this method.
     */
    public function give(string|Closure $implementation): void
    {
        if ($this->dependency === null) {
            throw new LogicException('A contextual dependency must be specified before using needs().');
        }

        $this->container->addContextualBinding(
            $this->consumers,
            $this->dependency,
            $implementation
        );
    }
}
