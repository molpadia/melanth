<?php

namespace Melanth\Routing;

use Melanth\Http\Request;

class RouteParameterBinder
{
    /**
     * The route instance.
     *
     * @var \Melanth\Routing\Route
     */
    protected $route;

    /**
     * Create a new route instance.
     *
     * @param \Melanth\Routing\Route $route The route instance.
     *
     * @return void
     */
    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    /**
     * Bind the parameters with route names.
     *
     * @param \Melanth\Http\Request $request The request instance.
     *
     * @return array
     */
    public function bind(Request $request) : array
    {
        $path = '/'.ltrim(rawurldecode($request->path()), '/');

        preg_match($this->route->getRegex(true), $path, $matches);

        return $this->matchKeys(array_slice($matches, 1));
    }

    /**
     * Combine a set of parameter maches with the route's keys.
     *
     * @param array $matches The mixure of match items.
     *
     * @return array
     */
    protected function matchKeys(array $matches) : array
    {
        if (empty($parameterNames = $this->route->parameterNames())) {
            return [];
        }

        $parameters = array_intersect_key($matches, array_flip($parameterNames));

        return array_filter($parameters);
    }
}
