<?php

namespace Melanth\Tests\Facades;

use Exception;
use RuntimeException;
use Melanth\Contracts\Routing\Router as RouterContract;
use Melanth\Facades\Facade;
use Melanth\Facades\Router as RouterFacade;
use Melanth\Foundation\Application;
use Melanth\Routing\Router;
use Melanth\Tests\Foundation\TestCase;

class FacadeTest extends TestCase
{
    private $app;

    public function setUp() : void
    {
        $this->app = new Application;
        $this->app->instance(FacadeBinding::class, new FacadeBinding('foo'));

        Facade::setApplication($this->app);
    }

    public function testCallInstanceDirectly()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Facade does not implement getAccessor method');

        Facade::getAccessor();
    }

    public function testCallInstanceMethodBasicUsage()
    {
        $this->assertSame('foo', FacadeStub::get());
    }

    public function testGetApplicaiton()
    {
        $this->assertInstanceOf(Application::class, FacadeStub::getApplication());
    }
}

class FacadeStub extends Facade
{
    protected static function getAccessor()
    {
        return FacadeBinding::class;
    }
}

class FacadeBinding
{
    private $stub;

    public function __construct($stub)
    {
        $this->stub = $stub;
    }

    public function get()
    {
        return $this->stub;
    }
}
