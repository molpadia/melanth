<?php

namespace Melanth\Contracts\Container;

interface Container
{
    /**
     * Set a concrete instance or constant value to a stack.
     *
     * @param string $name     The binding name.
     * @param mixed  $concrete The concrete instance.
     *
     * @return void
     */
    public function instance(string $name, $concrete) : void;

    /**
     * Register a binding with the container.
     *
     * @param string $abstract The binding name stores entity.
     * @param mixed  $concrete The concrete instance.
     *
     * @return void
     */
    public function bind(string $abstract, $concrete = null) : void;

    /**
     * Determine whether the given concrete class exists in the container.
     *
     * @param string $abstract The abstract type.
     *
     * @return bool
     */
    public function bound(string $abstract) : bool;

    /**
     * Resolve a concrete instance from the container.
     *
     * @param string $abstract The given abstract type.
     *
     * @return mixed
     */
    public function make($abstract);

    /**
     * Resolve a concrete instance with attached parameters from the container.
     *
     * @param string $abstract   The given abstract type.
     * @param array  $parameters The rest parameters.
     *
     * @return mixed
     */
    public function makeWith($concrete, array $parameters = []);

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
    public function build($concrete);

    /**
     * Set alias type with abstract class.
     *
     * @param string $abstract The abstract naming.
     * @param string $alias    The alias name.
     *
     * @return void
     */
    public function alias(string $abstract, string $alias) : void;

    /**
     * Get a registered alias with the abstract type.
     *
     * @param string $abstract The given abstract type.
     *
     * @return string
     */
    public function getAlias(string $abstract) : string;
}
