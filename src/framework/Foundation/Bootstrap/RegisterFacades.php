<?php

namespace Melanth\Foundation\Bootstrap;

use Melanth\Contracts\Foundation\Bootstrapper;
use Melanth\Foundation\Application;
use Melanth\Facades\Facade;

class RegisterFacades implements Bootstrapper
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
        Facade::setApplication($app);
    }
}
