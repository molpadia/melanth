<?php

namespace Melanth\Foundation\Bootstrap;

use Melanth\Contracts\Foundation\Bootstrapper;
use Melanth\Foundation\Application;

class RegisterProviders implements Bootstrapper
{
    /**
     * Bootstrap the application service.
     *
     * @param \Melanth\Foundation\Application $app The application instance.
     *
     * @return void
     */
    public function bootstrap(Application $app) : void
    {
        foreach ($app['config']['app.providers'] as $provider) {
            $app->register($provider);
        }
    }
}
