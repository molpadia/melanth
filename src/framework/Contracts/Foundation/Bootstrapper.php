<?php

namespace Melanth\Contracts\Foundation;

use Melanth\Foundation\Application;

interface Bootstrapper
{
    /**
     * Bootstrap the application service.
     *
     * @param \Melanth\Foundation\Application $app The application instance.
     *
     * @return void
     */
    public function bootstrap(Application $app) : void;
}
