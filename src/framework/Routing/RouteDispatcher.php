<?php

namespace Melanth\Routing;

use Reflector;
use ReflectionFunction;
use ReflectionMethod;
use Melanth\Container\Container;
use Melanth\Support\Str;

class RouteDispatcher
{
    use RouteDependencyResolver;

    /**
     * The container instance.
     *
     * @var \Melanth\Container\Container
     */
    protected $container;

    /**
     * Create a new route dispatcher instance.
     *
     * @param \Melanth\Container\Container $container The container instance.
     *
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Dispatch an action with the route.
     *
     * @param \Melanth\Routing\Route $route The route instance.
     *
     * @return mixed
     */
    public function dispatch(Route $route)
    {
        if (! isset($route->getAction()['controller'])) {
            return $this->dispatchCallable($route);
        }

        return $this->dispatchController($route);
    }

    /**
     * Dispatch a callable action.
     *
     * @param \Melanth\Routing\Route $route The route instance.
     *
     * @return mixed
     */
    protected function dispatchCallable(Route $route)
    {
        $callable = $route->getAction()['uses'];

        return $callable(...$this->resolveMethodDependencies(
            new ReflectionFunction($callable), $route->parametersWithoutEmpty()
        ));
    }

    /**
     * Dispatch a controller action.
     *
     * @param \Melanth\Routing\Route $route The route instance.
     *
     * @return mixed
     */
    protected function dispatchController(Route $route)
    {
        [$classname, $method] = $this->parseController($route);

        $controller = $this->container->make(ltrim($classname, '\\'));

        return $controller->{$method}(...$this->resolveMethodDependencies(
            new ReflectionMethod($controller, $method), $route->parametersWithoutEmpty()
        ));
    }

    /**
     * Parse the controller action.
     *
     * @param \Melanth\Routing\Route $route The route instance.
     *
     * @return array
     */
    protected function parseController(Route $route) : array
    {
        return Str::parseCallback($route->getAction()['uses']);
    }
}
