<?php

namespace Melanth\Tests\Routing;

use Melanth\Foundation\Application;
use Melanth\Routing\Router;
use Melanth\Routing\RouteServiceProvider;
use Melanth\Tests\Foundation\TestCase;

class RouteServiceProviderTest extends TestCase
{
    private $app;
    private $provider;

    public function setUp() : void
    {
        parent::setUp();

        $this->app = new Application;
        $this->provider = new RouteServiceProvider($this->app);
    }

    public function tearDown() : void
    {
        parent::tearDown();
    }

    public function testRegister()
    {
        $this->assertNull($this->provider->register());

        $this->assertInstanceOf(Router::class, $this->app['router']);
    }
}
