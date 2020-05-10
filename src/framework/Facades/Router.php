<?php

namespace Melanth\Facades;

use Melanth\Contracts\Routing\Router as RouterContract;

class Router extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getAccessor()
    {
        return RouterContract::class;
    }
}
