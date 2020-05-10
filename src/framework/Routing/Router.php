<?php

namespace Melanth\Routing;

use Closure;
use Melanth\Container\Container;
use Mealnth\Contracts\Routing\Router as RouterContract;
use Melanth\Contracts\Support\Arrayable;
use Melanth\Http\Request;
use Melanth\Http\Response;
use Melanth\Http\Exceptions\NotFoundHttpException;
use Melanth\Support\Arr;
use Melanth\Support\Pipeline;

class Router implements RouterContract
{
    /**
     * The dependency container instance.
     *
     * @var \Melanth\Container\Container
     */
    protected $container;

    /**
     * The route group stack.
     *
     * @var array
     */
    protected $groupStack = [];

    /**
     * The flattened route stack.
     *
     * @var array
     */
    protected $routes = [];

    /**
     * The middleware list stack.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * Create a new router instance.
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
     * Register a GET HTTP method request with the router.
     *
     * @param string $uri    The route URI.
     * @param mixed  $action The action with the router.
     *
     * @return \Melanth\Routing\Route
     */
    public function get(string $uri, $action) : Route
    {
        return $this->addRoute(['GET'], $uri, $action);
    }

    /**
     * Register a POST HTTP method request with the router.
     *
     * @param string $uri    The route URI.
     * @param mixed  $action The action with the router.
     *
     * @return \Melanth\Routing\Route
     */
    public function post(string $uri, $action) : Route
    {
        return $this->addRoute(['POST'], $uri, $action);
    }

    /**
     * Register a PUT HTTP method request with the router.
     *
     * @param string $uri    The route URI.
     * @param mixed  $action The action with the router.
     *
     * @return \Melanth\Routing\Route
     */
    public function put(string $uri, $action) : Route
    {
        return $this->addRoute(['PUT'], $uri , $action);
    }

    /**
     * Register a PATCH HTTP method request with the router.
     *
     * @param string $uri    The route URI.
     * @param mixed  $action The action with the router.
     *
     * @return \Melanth\Routing\Route
     */
    public function patch(string $uri, $action) : Route
    {
        return $this->addRoute(['PATCH'], $uri, $action);
    }

    /**
     * Register a DELETE HTTP method request with the router.
     *
     * @param string $uri    The route URI.
     * @param mixed  $action The action with the router.
     *
     * @return \Melanth\Routing\Route
     */
    public function delete(string $uri, $action) : Route
    {
        return $this->addRoute(['DELETE'], $uri, $action);
    }

    /**
     * Register a HEAD HTTP method request with the router.
     *
     * @param string $uri    The route URI.
     * @param mixed  $action The action with the router.
     *
     * @return \Melanth\Routing\Route
     */
    public function head(string $uri, $action) : Route
    {
        return $this->addRoute(['HEAD'], $uri, $action);
    }

    /**
     * Register a OPTIONS HTTP method request with the router.
     *
     * @param string $uri    The route URI.
     * @param mixed  $action The action with the router.
     *
     * @return \Melanth\Routing\Route
     */
    public function options(string $uri, $action) : Route
    {
        return $this->addRoute(['OPTIONS'], $uri, $action);
    }

    /**
     * Register any HTTP method requests with the router.
     *
     * @param array|string $methods The HTTP methods.
     * @param string       $uri     The route URI.
     * @param mixed        $action  The action with the router.
     *
     * @return \Melanth\Routing\Route
     */
    public function any($methods, string $uri, $action) : Route
    {
        return $this->addRoute(array_map('strtoupper', (array) $methods), $uri, $action);
    }

    /**
     * Create a route and store to the collection.
     *
     * @param array|string $methods The HTTP methods.
     * @param string       $uri     The route URI.
     * @param mixed        $action  The action with the router.
     *
     * @return \Melanth\Routing\Route
     */
    protected function addRoute($methods, string $uri, $action) : Route
    {
        $route = $this->createRoute($methods, $uri, $action);

        $domainAndUri = $route->domain().$route->uri();

        foreach ($route->methods() as $method) {
            $this->routes[$method][$domainAndUri] = $route;
        }

        return $route;
    }

    /**
     * Create a new route instance.
     *
     * @param array|string $methods The HTTP methods.
     * @param string       $uri     The route URI.
     * @param mixed        $action  The action with the router.
     *
     * @return \Melanth\Routing\Route
     */
    protected function createRoute($methods, string $uri, $action) : Route
    {
        if ($this->isControllerAction($action)) {
            $action = $this->convertToControllerAction($action);
        }

        $route = $this->newRoute($methods, $this->prefix($uri), $action);

        return $this->mergeRouteGroup($route);
    }

    /**
     * Determine whtether the action references controller.
     *
     * @param mixed $action The route action.
     *
     * @return bool
     */
    protected function isControllerAction($action) : bool
    {
        if ($action instanceof Closure) {
            return false;
        }

        return is_string($action) || (is_array($action) && isset($action['uses']));
    }

    /**
     * Add a controller based on route action to the route.
     *
     * @param array|string|\Closure $action The controller action.
     *
     * @return array
     */
    protected function convertToControllerAction($action) : array
    {
        if (is_string($action)) {
            $action = ['uses' => $action];
        }

        if ($this->groupStack) {
            $action['uses'] = $this->prependGroupNamespace($action['uses']);
        }

        $action['controller'] = $action['uses'];

        return $action;
    }

    /**
     * Prepend the namespace from the group.
     *
     * @param string $controller The controller clsss name.
     *
     * @return string
     */
    protected function prependGroupNamespace(string $controller) : string
    {
        $group = end($this->groupStack);

        if (isset($group['namespace'])) {
            $controller = $group['namespace'].'\\'.$controller;
        }

        return $controller;
    }

    /**
     * Merge the last group stack with the route.
     *
     * @param \Melanth\Routing\Route $route The route instance.
     *
     * @return \Melanth\Routing\Route
     */
    protected function mergeRouteGroup(Route $route) : Route
    {
        if (! empty($this->groupStack)) {
            $group = end($this->groupStack);

            $route->setAction(array_merge($route->getAction(), $group));
        }

        return $route;
    }

    /**
     * Create a new rotue instance.
     *
     * @param array|string $methods The HTTP methods.
     * @param string       $uri     The route URI.
     * @param mixed        $action  The action with the router.
     *
     * @return \Melanth\Routing\Route
     */
    protected function newRoute($methods, string $uri, $action) : Route
    {
        return new Route($methods, $uri, $action);
    }

    /**
     * Group the routes with the router.
     *
     * @param array           $attributes The routing attributes.
     * @param string|\Closure $routes     The routes to be registered.
     *
     * @return void
     */
    public function group(array $attributes, $routes) : void
    {
        $this->groupStack[] = $attributes;

        $this->loadRoutes($routes);

        array_pop($this->groupStack);
    }

    /**
     * Load the routes with the router.
     *
     * @param string|\Closure $routes The routes to be registered.
     *
     * @return void
     */
    protected function loadRoutes($routes) : void
    {
        if ($routes instanceof Closure) {
            $routes($this);
        } else {
            $this->container['files']->require($routes);
        }
    }

    /**
     * Get the uri with calibration.
     *
     * @param string $uri The route URI.
     *
     * @return string
     */
    protected function prefix(string $uri) : string
    {
        return '/'.trim(trim($this->lastPrefix(), '/').'/'.trim($uri, '/'), '/');
    }

    /**
     * Get the last prefix from the group stack.
     *
     * @return string|null
     */
    public function lastPrefix()
    {
        if ($this->groupStack) {
            $group = end($this->groupStack);

            return $group['prefix'] ?? '';
        }
    }

    /**
     * Dispatch an incoming request to the controller.
     *
     * @param \Melanth\Http\Request $request The request instance.
     *
     * @return \Melanth\Http\Response
     */
    public function dispatch(Request $request) : Response
    {
        return $this->runRoute($request, $this->findRoute($request));
    }

    /**
     * Find the route by incoming request.
     *
     * @param \Melanth\Http\Request The request instance.
     *
     * @return \Melanth\Routing\Route
     *
     * @throws \Melanth\Http\NotFoundHttpException
     */
    public function findRoute(Request $request) : Route
    {
        $routes = $this->getRoutes($request->getMethod());

        $route = Arr::first($routes, function ($route) use ($request) {
            return $route->matches($request);
        });

        if (! is_null($route)) {
            return $route->bind($request);
        }

        throw new NotFoundHttpException;
    }

    /**
     * Get the routes with the router.
     *
     * @param string|null $mehtod The HTTP method.
     *
     * @return array
     */
    public function getRoutes($method = null) : array
    {
        return ! is_null($method) ? $this->routes[$method] ?? [] : $this->routes;
    }

    /**
     * Run the route with the router.
     *
     * @param \Melanth\Http\Request  $request The request instance.
     * @param \Melanth\Routing\Route $route   The route instance.
     *
     * @return \Melanth\Http\Response
     */
    protected function runRoute(Request $request, Route $route) : Response
    {
        return (new Pipeline($this->container))
                ->via($request)
                ->through($this->middleware)
                ->then(function ($request) use ($route) {
                    return static::toResponse($request, $this->dispatchRoute($route));
                });
    }

    /**
     * Dispatch the route into the controller.
     *
     * @param \Melanth\Routing\Route $route The route instance.
     *
     * @return mixed
     */
    protected function dispatchRoute(Route $route)
    {
        return (new RouteDispatcher($this->container))->dispatch($route);
    }

    /**
     * Resolve an incoming response.
     *
     * @param \Melanth\Http\Request $request  The request instance.
     * @param mixed                 $response The response instance.
     *
     * @return \Melanth\Http\Response
     */
    public static function toResponse(Request $request, $response) : Response
    {
        if (! $response instanceof Response) {
            $response = new Response($response);
        }

        return $response->prepare($request);
    }

    /**
     * Dynamically handle calls into the router.
     *
     * @param string $method     The custom method.
     * @param array  $parameters The input parameters.
     *
     * @return \Melanth\Routing\RouteRegistrator
     */
    public function __call(string $mehtod, array $parameters)
    {
        return (new RouteRegistrator($this))->attribute($mehtod, ...$parameters);
    }
}
