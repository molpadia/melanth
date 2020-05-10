<?php

namespace Melanth\Foundation;

use Melanth\Container\Container;
use Melanth\Contracts\Foundation\Application as ApplicationContract;
use Melanth\Contracts\Http\Kernel as KernelContract;
use Melanth\Contracts\Http\Request;
use Melanth\Contracts\Http\Response;
use Melanth\Routing\RouteServiceProvider;
use Melanth\Support\ServiceProvider;

class Application extends Container implements ApplicationContract
{
    /**
     * The base path of the application.
     *
     * @var string
     */
    protected $basePath;

    /**
     * The service provider mapping.
     *
     * @var array
     */
    protected $providers = [];

    /**
     * Determine whether all of the service providers are bootstrapped.
     *
     * @var bool
     */
    protected $booted = false;

    /**
     * Create a new application instance.
     *
     * @param string|null $basePath The base path.
     *
     * @return void
     */
    public function __construct(string $basePath = null)
    {
        if ($basePath) {
            $this->setBasePath($basePath);
        }

        $this->registerBaseBinding();
        $this->registerBaseServiceProviders();
        $this->registerContainerAliases();
    }

    /**
     * Set the base path to the application.
     *
     * @param string $basePath The base path.
     *
     * @return \Melanth\Foundation\Application
     */
    public function setBasePath(string $basePath) : Application
    {
        $this->basePath = rtrim($basePath, '\/');

        $this->mountApplicationPath();

        return $this;
    }

    /**
     * Bind all of the application paths in the container.
     *
     * @return void
     */
    protected function mountApplicationPath() : void
    {
        $this->instance('path', $this->path());
        $this->instance('path.app', $this->appPath());
        $this->instance('path.config', $this->configPath());
        $this->instance('path.bootstrap', $this->bootstrapPath());
    }

    /**
     * Get the absolute path.
     *
     * @param string $path The path append to the base path.
     *
     * @return string
     */
    public function path(string $path = '') : string
    {
        return $this->basePath.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }

    /**
     * Get the path of app directory.
     *
     * @param string $path The path append to the app path.
     *
     * @return string
     */
    public function appPath(string $path = '')
    {
        return $this->path('app'.($path ? DIRECTORY_SEPARATOR.$path : $path));
    }

    /**
     * Get the path of bootstrap directory.
     *
     * @param string $path The path append to the bootstrap path.
     *
     * @return string
     */
    public function bootstrapPath(string $path = '')
    {
        return $this->path('bootstrap'.($path ? DIRECTORY_SEPARATOR.$path : $path));
    }

    /**
     * Get the path of config directory.
     *
     * @param string $path The path append to the bootstrap path.
     *
     * @return string
     */
    public function configPath($path = '')
    {
        return $this->path('config'.($path ? DIRECTORY_SEPARATOR.$path : $path));
    }

    /**
     * Register the application into the container.
     *
     * @return void
     */
    protected function registerBaseBinding() : void
    {
        $this->setInstance($this);
        $this->instance('app', $this);
        $this->instance(Containr::class, $this);
    }

    /**
     * Register the base service providers into the container.
     *
     * @return void
     */
    protected function registerBaseServiceProviders() : void
    {
        $this->register(RouteServiceProvider::class);
    }

    /**
     * Register the core base bindings aliases into the container.
     *
     * @return void
     */
    protected function registerContainerAliases() : void
    {
        foreach ([
            'app'    => [\Melanth\Foundation\Application::class, \Melanth\Contracts\Foundation\Application::class],
            'config' => [\Melanth\Foundation\Config::class],
            'router' => [\Melanth\Routing\Router::class, \Melanth\Contracts\Routing\Router::class],
        ] as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($key, $alias);
            }
        }
    }

    /**
     * Handle an incoming request in the application.
     *
     * @param \Melanth\Http\Request $request The request instance.
     *
     * @return \Melanth\Http\Response
     */
    public function handle(Request $request) : Response
    {
        return $this[KernelContract::class]->handle($request);
    }

    /**
     * Register a service provider.
     *
     * @param string|\Melanth\Support\ServiceProvider $provider The service provider.
     *
     * @return \Melanth\Support\ServiceProvider
     */
    public function register($provider) : ServiceProvider
    {
        if ($registered = $this->getProvider($provider)) {
            return $registered;
        }

        if (is_string($provider)) {
            $provider = new $provider($this);
        }

        $provider->register();

        return $this->providers[get_class($provider)] = $provider;
    }

    /**
     * Get the service provider.
     *
     * @param string|\Melanth\Support\ServiceProvider $provider The service provider.
     *
     * @return \Melanth\Support\ServiceProvider
     */
    public function getProvider($provider) : ?ServiceProvider
    {
        $provider = is_string($provider) ? $provider : get_class($provider);

        return $this->providers[$provider] ?? null;
    }

    /**
     * Bootstrap all of the service providers.
     *
     * @return void
     */
    public function boot() : void
    {
        if ($this->booted) {
            return;
        }

        foreach (array_values($this->providers) as $provider) {
            $provider->boot();
        }

        $this->booted = true;
    }

    /**
     * Determine whether the service providers are bootstrapped
     *
     * @return bool
     */
    public function isBooted() : bool
    {
        return $this->booted;
    }
}
