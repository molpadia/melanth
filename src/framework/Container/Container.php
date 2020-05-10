<?php

namespace Melanth\Container;

use ArrayAccess;
use Closure;
use Exception;
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use RuntimeException;
use LogicException;
use UnexpectedValueException;
use Melanth\Contracts\Container\Container as ContainerContract;

class Container implements ArrayAccess, ContainerContract
{
    /**
     * The instantiated instance.
     *
     * @var \Melanth\Container\Container
     */
    protected static $instance;

    /**
     * The registered aliases.
     *
     * @var array
     */
    protected $aliases = [];

    /**
     * The types that have been resolved.
     *
     * @var array
     */
    protected $instances = [];

    /**
     * The bindings entities.
     *
     * @var array
     */
    protected $bindings = [];

    /**
     * The associative stack to override constructor parameters.
     *
     * @var array
     */
    protected $overrides = [];

    /**
     * A mapping stores binding extension.
     *
     * @var array
     */
    protected $extenders = [];

    /**
     * Make an initiated object to global instance.
     *
     * @param \Melanth\Container\Container $instance The initiated object.
     *
     * @return \Melanth\Container\Container
     */
    public static function setInstance($instance) : Container
    {
        return static::$instance = $instance;
    }

    /**
     * Get the container by global method.
     *
     * @return \Melanth\Container\Container
     */
    public static function getInstance() : Container
    {
        if (! static::$instance) {
            static::$instance = new static;
        }

        return static::$instance;
    }

    /**
     * Set a concrete instance or constant value to a stack.
     *
     * @param string $name     The binding name.
     * @param mixed  $concrete The concrete instance.
     *
     * @return void
     */
    public function instance(string $name, $concrete) : void
    {
        $this->instances[$name] = $concrete;
    }

    /**
     * Register a binding with the container.
     *
     * @param string $abstract The binding name stores entity.
     * @param mixed  $concrete The concrete instance.
     *
     * @return void
     */
    public function bind(string $abstract, $concrete = null) : void
    {
        $abstract = $this->getAlias($abstract);

        if (is_null($concrete)) {
            $concrete = $abstract;
        }

        // If the given concrete is not closure, it means the given type is a classname
        // and needs to be bound to the container.
        if (! $concrete instanceof CLosure) {
            $concrete = $this->prepareConcrete($concrete);
        }

        $this->bindings[$abstract] = $concrete;
    }

    /**
     * Wrap concrete class inside with a closure to provide flexibility when extending bindings.
     *
     * @param mixed $concrete The concrete instance classname.
     *
     * @return \Closure
     */
    protected function prepareConcrete($concrete) : Closure
    {
        return function ($container, $parameters = []) use ($concrete) {
            return $container->build($concrete, $parameters);
        };
    }

    /**
     * Determine whether the given concrete class exists in the container.
     *
     * @param string $abstract The abstract type.
     *
     * @return bool
     */
    public function bound(string $abstract) : bool
    {
        $abstract = $this->getAlias($abstract);

        return isset($this->bindings[$abstract]) || isset($this->instances[$abstract]);
    }

    /**
     * Resolve a concrete instance from the container.
     *
     * @param string $abstract The given abstract type.
     *
     * @return mixed
     */
    public function make($abstract)
    {
        return $this->resolve($abstract);
    }

    /**
     * Resolve a concrete instance with attached parameters from the container.
     *
     * @param string $abstract   The given abstract type.
     * @param array  $parameters The rest parameters.
     *
     * @return mixed
     */
    public function makeWith($concrete, array $parameters = [])
    {
        return $this->resolve($concrete, $parameters);
    }

    /**
     * Resolve the derived abstract with container.
     *
     * @param string $abstract   The given abstract type.
     * @param array  $parameters The given arguemnts of constructor.
     *
     * @return mixed
     */
    public function resolve(string $abstract, array $parameters = [])
    {
        $abstract = $this->getAlias($abstract);

        // if an instance of the type is currently being used as a singleton,
        // just return an existing instance.
        if (isset($this->instances[$abstract]) && ! $parameters) {
            return $this->instances[$abstract];
        }

        $this->overrides[] = $parameters;

        $concrete = $this->buildConcrete($abstract);

        foreach ($this->getExtenders($abstract) as $extender) {
            $concrete = $extender($concrete, $this);
        }

        array_pop($this->overrides);

        return $this->instances[$abstract] = $concrete;
    }

    /**
     * Create a concrete class with the abstract type.
     *
     * @param string $abstract The given abstract type.
     *
     * @return mixed
     */
    protected function buildConcrete(string $abstract)
    {
        return $this->build($this->bindings[$abstract] ?? $abstract);
    }

    /**
     * Initiate a concrete instance.
     * If the concrete is actually a closure, just execute the function,
     * which allows the method to be used as a resolver for more fine-tuned resolution,
     * otherwise, using a reflection class to extract the dependencies
     * and inject the parameters' bindings into the constructor.
     *
     * @param string|\Closure $concrete The concrete binding.
     *
     * @return mixed
     */
    public function build($concrete)
    {
        if ($concrete instanceof Closure) {
            return $concrete($this, $this->lastParameters());
        }

        $reflector = new ReflectionClass($concrete);

        if (! $reflector->isInstantiable()) {
            throw new LogicException("Unrecognized class {$concrete}");
        }

        if (is_null($constructor = $reflector->getConstructor())) {
            return $reflector->newInstance();
        }

        $dependencies = $this->resolveDependencies($constructor->getParameters());

        return $reflector->newInstanceArgs($dependencies);
    }

    /**
     * Resolve a typing dependencies to the constructor's parameters.
     *
     * @param array $dependencies The given reflection parameters.
     *
     * @return array
     */
    protected function resolveDependencies(array $dependencies) : array
    {
        $results = [];

        foreach ($dependencies as $dependency) {
            $parameters = $this->lastParameters();

            // If the parameter name exists in overrided paratmers, add into the stack.
            if (array_key_exists($dependency->name, $parameters)) {
                $results[] = $parameters[$dependency->name];

                continue;
            }

            $results[] = is_null($class = $dependency->getClass())
                ? $this->resolvePrimitive($dependency)
                : $this->make($class->name);
        }

        return $results;
    }

    /**
     * Resolve a non-class primitive dependency.
     *
     * @param \ReflectionParameter $parameter The reflection parameter.
     *
     * @return mixed
     *
     * @throws \UnexpectedValueException
     */
    protected function resolvePrimitive(ReflectionParameter $parameter)
    {
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new \UnexpectedValueException('Undefiend default value of parameter.');
    }

    /**
     * Get the last overridden parameters from a stack.
     *
     * @return array
     */
    protected function lastParameters() : array
    {
        return count($this->overrides) ? end($this->overrides) : [];
    }

    /**
     * Get the extenders by given abstract type.
     *
     * @param string $abstract The given abstract type.
     *
     * @return array
     */
    protected function getExtenders(string $abstract) : array
    {
        return ! empty($this->extenders[$abstract]) ? $this->extenders[$abstract] : [];
    }

    /**
     * Extend a class binding to the stack.
     *
     * @param string   $abstract The given abstract type.
     * @param \Closure $closure  A closure contains binding implementation.
     *
     * @return $this
     */
    public function extend(string $abstract, Closure $closure) : self
    {
        $this->extenders[$abstract][] = $closure;

        return $this;
    }

    /**
     * Set alias type with abstract class.
     *
     * @param string $abstract The abstract naming.
     * @param string $alias    The alias name.
     *
     * @return void
     */
    public function alias(string $abstract, string $alias) : void
    {
        if ($abstract === $alias) {
            throw new LogicException("[{$abstract}] cannot alias itself.");
        }

        $this->aliases[$alias] = $abstract;
    }

    /**
     * Get a registered alias with the abstract type.
     *
     * @param string $abstract The given abstract type.
     *
     * @return string
     */
    public function getAlias(string $abstract) : string
    {
        return $this->aliases[$abstract] ?? $abstract;
    }

    /**
     * Set the given value at specified offset.
     *
     * @param string $key   The binding name.
     * @param mixed  $value The concrete value.
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->bind($key, $value instanceof Closure ? $value : function () use ($value) {
            return $value;
        });
    }

    /**
     * Get the given value with the container.
     *
     * @param string $key The binding name.
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->make($key);
    }

    /**
     * Determine whether the given key exists in the container.
     *
     * @param string $key The given key.
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->bound($key);
    }

    /**
     * Remove the bindings at a given offset.
     *
     * @param string $key The given key name.
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->bindings[$key], $this->instances[$key]);
    }
}
