<?php

declare(strict_types=1);

namespace mykemeynell\Reflector\Application;

use Closure;
use JetBrains\PhpStorm\Pure;
use mykemeynell\Reflector\Bindings\ContextualBindingBuilder;
use mykemeynell\Reflector\Exceptions\ContainerException;
use mykemeynell\Reflector\Exceptions\NotFoundException;
use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use RuntimeException;

final class Container implements ContainerInterface
{
    /**
     * @var array<class-string|string, array {
     *     concrete: class-string|Closure|object,
     *     singleton: bool
     * }>
     */
    private array $bindings = [];

    /**
     * @var array<class-string|string, object>
     */
    private array $instances = [];

    /**
     * @var array<class-string, array<class-string|string, class-string|Closure|object>>
     */
    private array $contextual = [];

    /**
     * @var array<class-string|string>
     */
    private array $resolving = [];

    private static self $instance;

    /**
     * Constructor method that initializes the class instance.
     *
     * @return void
     */
    public function __construct()
    {
        self::$instance = $this;
    }

    /**
     * Retrieves the singleton instance of the class.
     *
     * @return self The single instance of the class.
     */
    public static function getInstance(): self
    {
        return self::$instance ??= new self;
    }

    /**
     * {@inheritDoc}
     */
    public function get(string $id)
    {
        return $this->make($id);
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $id): bool
    {
        return isset($this->instances[$id])
            || isset($this->bindings[$id])
            || class_exists($id);
    }

    /**
     * Binds an abstract type to a concrete implementation or a closure.
     *
     * @param  string  $abstract  The abstract type or identifier being bound.
     * @param  string|Closure|null  $concrete  The concrete implementation or closure. Defaults to the abstract type
     *                                         if null.
     */
    public function bind(
        string $abstract,
        string|Closure|null $concrete = null
    ): void {
        if (isset($this->instances[$abstract])) {
            unset($this->instances[$abstract]);
        }

        $this->bindings[$abstract] = self::bindingToArray(
            concrete: $concrete ?? $abstract,
            singleton: false
        );
    }

    /**
     * Binds an abstract type to a concrete implementation in the container as a singleton.
     *
     * @param  string  $abstract  The abstract type that is being bound.
     * @param  string|Closure|null  $concrete  The concrete implementation or factory closure. If null, the abstract
     *                                         type will be used as its own concrete implementation.
     */
    public function singleton(
        string $abstract,
        string|Closure|null $concrete = null
    ): void {
        if (isset($this->instances[$abstract])) {
            unset($this->instances[$abstract]);
        }

        $this->bindings[$abstract] = self::bindingToArray(
            concrete: $concrete ?? $abstract,
            singleton: true
        );
    }

    /**
     * Binds an instance to the given abstract type.
     *
     * @param  string  $abstract  The abstract type to bind the instance to.
     * @param  object  $instance  The instance to bind to the abstract type.
     */
    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
    }

    /**
     * Defines a contextual binding for the given consumers.
     *
     * @param  string  ...$consumers  The list of consumer classes or identifiers for which the binding is being defined.
     * @return ContextualBindingBuilder An instance of ContextualBindingBuilder to define the specific binding rules.
     */
    #[Pure]
    public function when(string ...$consumers): ContextualBindingBuilder
    {
        return new ContextualBindingBuilder($this, $consumers);
    }

    /**
     * Add a contextual binding for the given consumers, dependency, and implementation.
     *
     * @param  array  $consumers  An array of classes or interfaces that consume the dependency.
     * @param  string  $dependency  The dependency to be resolved for the specified consumers.
     * @param  string|Closure  $implementation  The concrete implementation or closure to resolve the dependency.
     */
    public function addContextualBinding(
        array $consumers,
        string $dependency,
        string|Closure $implementation
    ): void {
        foreach ($consumers as $consumer) {
            $this->contextual[$consumer][$dependency] = $implementation;
        }
    }

    /**
     * Resolves and returns an instance of the specified abstract type.
     *
     * If the abstract type has already been resolved as a singleton, the
     * same instance is returned. Otherwise, the type's binding is resolved
     * and a new instance is created. Supports managing singleton objects
     * and detecting circular dependencies during resolution.
     *
     * @param  string  $abstract  The identifier of the abstract type to resolve.
     * @param  array  $parameters  Named parameters that are to be passed to the abstract during resolution.
     * @return mixed The resolved instance of the abstract type.
     *
     * @throws RuntimeException If a circular dependency is detected during resolution.
     */
    public function make(string $abstract, array $parameters = []): mixed
    {
        if (isset($this->instances[$abstract])) {
            return $this->instances[$abstract];
        }

        $binding = $this->bindings[$abstract] ?? self::bindingToArray($abstract, singleton: false);

        if (in_array($abstract, $this->resolving, strict: true)) {
            throw new RuntimeException(
                sprintf(
                    'Circular dependency detected while resolving [%s]: %s.',
                    $abstract,
                    implode(' -> ', [...$this->resolving, $abstract])
                )
            );
        }

        $this->resolving[] = $abstract;

        try {
            $object = $this->resolve($binding['concrete'], $parameters);
        } finally {
            array_pop($this->resolving);
        }

        if ($binding['singleton'] && is_object($object)) {
            $this->instances[$abstract] = $object;
        }

        return $object;
    }

    /**
     * Resolves a given concrete definition into an instance.
     *
     * @param  string|Closure  $concrete  The concrete definition to be resolved. This can be
     *                                    a class name as a string, or a Closure to invoke.
     * @param  array  $parameters  Named parameters that are to be passed during resolution.
     * @return mixed The resolved instance of the given concrete definition.
     */
    private function resolve(string|Closure $concrete, array $parameters = []): mixed
    {
        if ($concrete instanceof Closure) {
            return $concrete($this, $parameters);
        }

        if (is_object($concrete)) {
            return $concrete;
        }

        return $this->build($concrete, $parameters);
    }

    /**
     * Builds and returns an instance of the specified class.
     *
     * @param  string  $class  The fully qualified name of the class to instantiate.
     * @param  array  $parameters  Named parameters that are to be passed to the class constructor.
     * @return object An instance of the specified class.
     *
     * @throws RuntimeException If the class does not exist.
     * @throws RuntimeException If the class is not instantiable.
     */
    private function build(string $class, array $parameters = []): object
    {
        if (! class_exists($class)) {
            throw new NotFoundException(
                sprintf('Cannot resolve [%s]: class does not exist.', $class)
            );
        }

        $reflection = new ReflectionClass($class);

        if (! $reflection->isInstantiable()) {
            throw new ContainerException(
                sprintf('Cannot instantiate [%s]', $class)
            );
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return new $class;
        }

        $arguments = array_map(
            fn (ReflectionParameter $parameter) => $this->resolveParameter(
                $class, $parameter, $parameters
            ),
            $constructor->getParameters()
        );

        return $reflection->newInstanceArgs($arguments);
    }

    /**
     * Resolves the value for the given parameter, either by type-hint,
     * default value, or contextual bindings.
     *
     * @param  string  $consumer  The name of the consumer requesting the parameter resolution.
     * @param  ReflectionParameter  $parameter  The parameter to resolve.
     * @param  array  $parameters  Named parameters that are to be passed to the constructor.
     * @return mixed The resolved value for the parameter.
     *
     * @throws RuntimeException If the parameter cannot be resolved and no default value is available.
     */
    private function resolveParameter(
        string $consumer,
        ReflectionParameter $parameter,
        array $parameters = []
    ): mixed {
        if (array_key_exists($parameter->getName(), $parameters)) {
            return $parameters[$parameter->getName()];
        }

        $type = $parameter->getType();

        if (! $type instanceof ReflectionNamedType || $type->isBuiltin()) {
            if ($parameter->isDefaultValueAvailable()) {
                return $parameter->getDefaultValue();
            }

            throw new ContainerException(
                sprintf('Cannot resolve parameter [$%s] while building [%s]', $parameter->getName(), $consumer)
            );
        }

        $dependency = $type->getName();

        if (isset($this->contextual[$consumer][$dependency])) {
            return $this->resolve(
                $this->contextual[$consumer][$dependency]
            );
        }

        return $this->make($dependency);
    }

    /**
     * Converts the given binding information into an array format.
     *
     * @param  string|Closure  $concrete  The concrete implementation or a closure defining the binding.
     * @param  bool  $singleton  Indicates whether the binding should be treated as a singleton.
     * @return array{concrete: string, singelton: bool} An associative array containing the binding information.
     */
    private static function bindingToArray(string|Closure $concrete, bool $singleton): array
    {
        return compact('concrete', 'singleton');
    }
}
