<?php

namespace Melanth\Support;

use Melanth\Foundation\Application;

abstract class ServiceProvider
{
    /**
     * The application instance.
     *
     * @var \Melanth\Foundation\Application
     */
    protected $app;

    /**
     * Create a new service provider instance.
     *
     * @param \Melanth\Foundation\Application $app The application instance.
     *
     * @return void
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Register a service provider.
     *
     * @return void
     */
    abstract public function register() : void;

    /**
     * Bootstrap a service provider.
     *
     * @return void
     */
    abstract public function boot() : void;
}
