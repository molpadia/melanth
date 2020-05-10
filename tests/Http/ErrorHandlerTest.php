<?php

namespace Melanth\Tests\Http;

use LogicException;
use Melanth\Foundation\Application;
use Melanth\Foundation\Config;
use Melanth\Http\ErrorHandler;
use Melanth\Http\Headers;
use Melanth\Http\Request;
use Melanth\Http\Response;
use Melanth\Http\Exceptions\HttpException;
use Melanth\Tests\Foundation\TestCase;

class ErrorHandlerTest extends TestCase
{
    private $app;
    private $handler;

    public function setUp() : void
    {
        parent::setUp();

        $this->app = new Application;
        $this->handler = new ErrorHandler($this->app);

        $this->app->instance('request', $this->createMock(Request::class));
        $this->app->instance('config', new Config);
    }

    public function tearDown() : void
    {
        parent::tearDown();
    }

    public function testRenderBasicUsage()
    {
        $headers = Headers::make();
        $response = $this->handler->render(new LogicException('Error occurred'));
        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('Error occurred', $response->getContent());
        $this->assertEquals($headers, $response->getHeaders());
    }

    public function testRenderExceptionWithJsonContent()
    {
        $headers = new Headers(['Content-Type' => 'application/json']);
        $this->app['request']->expects($this->once())
            ->method('isJson')
            ->will($this->returnValue(true));

        $this->app['config']->set('app.debug', false);

        $response = $this->handler->render(new LogicException('Error occurred'));
        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame('{"message":"Error occurred"}', $response->getContent());
        $this->assertEquals($headers, $response->getHeaders());
    }

    public function testRenderExceptionWithJsonContentInDebugMode()
    {
        $this->app['request']->expects($this->once())
            ->method('isJson')
            ->will($this->returnValue(true));

        $this->app['config']->set('app.debug', true);

        $headers = new Headers(['Content-Type' => 'application/json']);
        $response = $this->handler->render(new LogicException('Error occurred'));
        $this->assertSame(500, $response->getStatusCode());
        $this->assertSame(['message', 'exception', 'file', 'line', 'trace'], array_keys(json_decode($response->getContent(), true)));
        $this->assertEquals($headers, $response->getHeaders());
    }
    public function testRenderHttpException()
    {
        $headers = new Headers(['Allow' => 'GET']);
        $exception = new HttpException(Response::HTTP_NOT_FOUND, 'Error occurred', new LogicException, $headers->all());
        $response = $this->handler->render($exception);
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame('Error occurred', $response->getContent());
        $this->assertEquals($headers, $response->getHeaders());
    }
}
