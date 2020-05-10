<?php

namespace Melanth\Foundation\Bootstrap;

use Melanth\Contracts\Foundation\Bootstrapper;
use Melanth\Foundation\Application;

class BootProviders implements Bootstrapper
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
        $app->boot();
    }
}
