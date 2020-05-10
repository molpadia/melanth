<?php

namespace Melanth\Routing;

use Closure;
use LogicException;
use UnexpectedValueException;

class RouteAction
{
    /**
     * Parse the given action with the route.
     *
     * @param mixed $action The action reference.
     *
     * @return array
     */
    public static function parse($action) : array
    {
        if (is_null($action)) {
            return static::missingAction();
        }

        if (is_callable($action)) {
            $action = ['uses' => $action];
        }
        // If the given action not found, check the argument is action.
        elseif (is_string($action['uses']) && strpos($action['uses'], '@') === false) {
            $action['uses'] = static::makeInvokable($action['uses']);
        }

        return $action;
    }

    /**
     * Get the route action if it does not exist.
     *
     * @return array
     */
    protected static function missingAction() : array
    {
        return ['uses' => function () {
            throw new LogicException("Route has no action.");
        }];
    }

    /**
     * Make an invokable action.
     *
     * @param string $action The route action source.
     *
     * @return string
     *
     * @throws \UnexpectedValueException
     */
    protected static function makeInvokable($action) : string
    {
        if (! method_exists($action, '__invoke')) {
            throw new UnexpectedValueException("Invalid route action {$action}");
        }

        return $action.'@__invoke';
    }
}
