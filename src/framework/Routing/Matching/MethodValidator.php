<?php

namespace Melanth\Routing\Matching;

use Melanth\Http\Request;
use Melanth\Routing\Route;

class MethodValidator
{
    /**
     * Validate a given rule against the request.
     *
     * @param \Melanth\Http\Route   $route   The route instance.
     * @param \Melanth\Http\Request @request The Http request instance.
     *
     * @return bool
     */
    public function validate(Route $route, Request $request) : bool
    {
        return in_array($request->method(), $route->methods());
    }
}
