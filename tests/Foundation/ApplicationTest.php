<?php

namespace Melanth\Tests\Foundation;

use Melanth\Contracts\Http\Kernel as KernelContract;
use Melanth\Foundation\Application;
use Melanth\Foundation\Http\Kernel;
use Melanth\Http\Request;
use Melanth\Http\Response;
use Melanth\Support\ServiceProvider;
use PHPUnit\Framework\TestCase;

class ApplicationTest extends TestCase
{
    public function testGetBasePath()
    {
        $app = new Application;
        $this->assertSame('/foo', $app->path('foo'));

        $app = new Application('/foo/');
        $this->assertSame('/foo/bar', $app->path('bar'));

        $app = new Application(__DIR__);
        $this->assertSame(__DIR__, $app->path());
        $this->assertSame(__DIR__.'/foo', $app->path('foo'));
        $this->assertSame(__DIR__.'/app', $app->appPath());
        $this->assertSame(__DIR__.'/config', $app->configPath());
        $this->assertSame(__DIR__.'/bootstrap', $app->bootstrapPath());

        $this->assertSame(__DIR__, $app['path']);
        $this->assertSame(__DIR__.'/app', $app['path.app']);
        $this->assertSame(__DIR__.'/bootstrap', $app['path.bootstrap']);
        $this->assertSame(__DIR__.'/config', $app['path.config']);
    }

    public function testHandleRequest()
    {
        $request = Request::create('/foo', 'GET');
        $response = new Response;

        $kernel = $this->createMock(Kernel::class);
        $kernel->expects($this->once())
            ->method('handle')
            ->with($this->equalTo($request))
            ->will($this->returnValue($response));

        $app = new Application;
        $app->instance(KernelContract::class, $kernel);

        $this->assertSame($response, $app->handle($request));
    }

    public function testRegisterServiceProvider()
    {
        $app = new Application;
        $provider = $app->register(ServiceProviderStub::class);
        $this->assertTrue(isset($app['foo']));
        $this->assertSame('foo', $app['foo']);
        $this->assertInstanceOf(ServiceProvider::class, $app->register($provider));
    }

    public function testBootstrapServiceProvider()
    {
        $app = new Application;
        $app->register(ServiceProviderStub::class);
        $this->assertFalse(isset($app['bar']));
        $this->assertFalse($app->isBooted());

        $this->assertNull($app->boot());
        $this->assertTrue($app->isBooted());
        $this->assertTrue(isset($app['bar']));
        $this->assertSame('bar', $app['bar']);
        $this->assertNull($app->boot());
    }
}

class ServiceProviderStub extends ServiceProvider
{
    public function register() : void
    {
        $this->app->bind('foo', function () {
            return 'foo';
        });
    }

    public function boot() : void
    {
        $this->app->bind('bar', function () {
            return 'bar';
        });
    }
}
