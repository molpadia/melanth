<?php

namespace Melanth\Routing\Matching;

use Melanth\Routing\Route;
use Melanth\Http\Request;

class UriValidator
{
    /**
     * Validate the given rule against a route and request.
     *
     * @param \Melanth\Routing\Route $route   The route instance.
     * @param \Melanth\Http\Request  $request The request instance.
     *
     * @return bool
     */
    public function validate(Route $route, Request $request) : bool
    {
        $path = $request->path() === '/' ? '/' : $request->path();

        return preg_match($route->getRegex(), rawurldecode($path));
    }
}
