<?php

namespace Melanth\Contracts\Foundation;

use Melanth\Support\ServiceProvider;

interface Application
{
    /**
     * Register a service provider.
     *
     * @param string|\Melanth\Support\ServiceProvider $provider The service provider.
     *
     * @return \Melanth\Support\ServiceProvider
     */
    public function register($provider) : ServiceProvider;

    /**
     * Bootstrap all of the service providers.
     *
     * @return void
     */
    public function boot() : void;
}
