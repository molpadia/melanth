<?php

namespace Melanth\Facades;

use RuntimeException;
use Melanth\Foundation\Application;

abstract class Facade
{
    /**
     * The application instance.
     *
     * @var \Melanth\Foundation\Application
     */
    protected static $app;

    /**
     * The resolved object instance.
     *
     * @var object
     */
    protected static $instance;

    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getAccessor()
    {
        throw new RuntimeException('Facade does not implement getAccessor method');
    }

    /**
     * Set application instance.
     *
     * @param \Melanth\Foundation\Application $app The application instance.
     *
     * @return $this
     */
    public static function setApplication(Application $app)
    {
        static::$app = $app;
    }

    /**
     * Get application instance.
     *
     * @return \Melanth\Foundation\Application
     */
    public static function getApplication() : Application
    {
        return static::$app;
    }

    /**
     * Resolve the given accessor instance.
     *
     * @return mixed
     */
    protected static function resolveInstance()
    {
        if (! isset(static::$instance)) {
            static::$instance = static::$app[static::getAccessor()];
        }

        return static::$instance;
    }

    /**
     * Dynamically call the facade method.
     *
     * @param string $method    The mehtod name.
     * @param array  $arguments The arguments.
     *
     * @return mixed
     */
    public static function __callStatic(string $method, array $arguments = [])
    {
        return static::resolveInstance()->$method(...$arguments);
    }
}
