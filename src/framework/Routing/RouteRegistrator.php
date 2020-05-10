<?php

namespace Melanth\Routing;

use BadMethodCallException;
use InvalidArgumentException;

class RouteRegistrator
{
    /**
     * The restricated attributes through the router.
     *
     * @var array
     */
    protected $allowedAttributes = ['domain', 'middleware', 'namespace', 'prefix'];

    /**
     * The group attributes passed by router.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The router instance.
     *
     * @var \Melanth\Routing\Router
     */
    protected $router;

    /**
     * Create a new route registrator instance.
     *
     * @param \Melanth\Routing\Router $router The router instance.
     *
     * @return void
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Set the attribute to the router.
     *
     * @param string $key   The key name.
     * @param mixed  $value The attribute value.
     *
     * @return $this
     */
    public function attribute(string $key, $value) : self
    {
        if (!in_array($key, $this->allowedAttributes)) {
            throw new InvalidArgumentException("The attribute {$key} does not exist.");
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Group the attibutes to the router.
     *
     * @param string|\Closure $routes The routes to be registered.
     *
     * @return void
     */
    public function group($routes) : void
    {
        $this->router->group($this->attributes, $routes);
    }

    /**
     * Dynamically handle calls into the router.
     *
     * @param string $method     The custom method.
     * @param array  $parameters The rest parameters.
     *
     * @return void
     *
     * @throws \BadMethodCallException
     */
    public function __call(string $method, array $parameters)
    {
        if (in_array($method, $this->allowedAttributes)) {
            return $this->attribute($method, $parameters[0]);
        }

        throw new BadMethodCallException("Method {$method} does not exist.");
    }
}
