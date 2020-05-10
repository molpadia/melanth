<?php

namespace Melanth\Foundation\Bootstrap;

use UnexpectedValueException;
use Melanth\Contracts\Foundation\Bootstrapper;
use Melanth\Filesystem\Finder;
use Melanth\Foundation\Application;
use Melanth\Foundation\Config;

class ConfigurationLoader implements Bootstrapper
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
        $app->instance('config', new Config);

        $this->loadConfigurationFiles($app);

        if (! isset($app['config']['app'])) {
            throw new UnexpectedValueException('Unable to load app configuration file.');
        }
    }

    /**
     * Load all of the configruation files.
     *
     * @param \Melanth\Foundation\Application $app The application instance.
     *
     * @return void
     */
    protected function loadConfigurationFiles(Application $app) : void
    {
        $pattern = '/^[^\.]+\.php$/';

        foreach (Finder::create()->name($pattern)->in($app->configPath()) as $file) {
            $app['config']->set($file->getBasename('.php'), require $file->getRealPath());
        }
    }
}
