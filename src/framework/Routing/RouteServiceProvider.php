<?php

namespace Melanth\Routing;

use Melanth\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Register a service provider.
     *
     * @return void
     */
    public function register() : void
    {
        $this->app->bind('router', function ($app) {
            return new Router($app);
        });
    }

    /**
     * Bootstrap a service provider.
     *
     * @return void
     */
    public function boot() : void
    {
        //
    }
}
