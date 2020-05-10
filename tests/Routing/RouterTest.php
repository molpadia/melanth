<?php

namespace Melanth\Test\Routing;

use BadMethodCallException;
use InvalidArgumentException;
use LogicException;
use ReflectionClass;
use UnexpectedValueException;
use PHPUnit\Framework\TestCase;
use Melanth\Container\Container;
use Melanth\Http\Request;
use Melanth\Http\Response;
use Melanth\Http\Exceptions\NotFoundHttpException;
use Melanth\Routing\Router;
use Melanth\Routing\Route;

class RouterTest extends TestCase
{
    public function testBindBasicRoutes()
    {
        $router = new Router(new Container);

        foreach (['get', 'post', 'put', 'patch', 'delete', 'head', 'options'] as $method) {
            $router->{$method}('foo/bar', function () {
                return 'Hello World';
            });
        }

        $this->assertSame('Hello World', $router->dispatch(Request::create('foo/bar', 'GET'))->getContent());
        $this->assertSame('Hello World', $router->dispatch(Request::create('foo/bar', 'POST'))->getContent());
        $this->assertSame('Hello World', $router->dispatch(Request::create('foo/bar', 'PUT'))->getContent());
        $this->assertSame('Hello World', $router->dispatch(Request::create('foo/bar', 'PATCH'))->getContent());
        $this->assertSame('Hello World', $router->dispatch(Request::create('foo/bar', 'DELETE'))->getContent());
        $this->assertSame('Hello World', $router->dispatch(Request::create('foo/bar', 'OPTIONS'))->getContent());
        $this->assertEmpty($router->dispatch(Request::create('foo/bar', 'HEAD'))->getContent());
    }

    public function testBindCustomRoutes()
    {
        $router = new Router(new Container);
        $router->any('purge', 'foo/bar', function () {
            return 'Hello World';
        });

        $this->assertSame('Hello World', $router->dispatch(Request::create('foo/bar', 'PURGE'))->getContent());

        $route = $router->any(['GET', 'POST'], 'foo/bar', function () {
            return 'hello';
        });

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame(['GET', 'POST'], $route->methods());
        $this->assertSame('/foo/bar', $route->uri());
        $this->assertSame('hello', $router->dispatch(Request::create('foo/bar', 'GET'))->getContent());
        $this->assertSame('hello', $router->dispatch(Request::create('foo/bar', 'POST'))->getContent());
    }

    public function testGetRoutesBasicUsage()
    {
        $router = new Router(new Container);
        $router->get('foo/bar', function () {
            return 'hello';
        });
        $router->post('foo/bar', function () {
            return 'hello';
        });

        $route1 = new Route(['GET'], '/foo/bar', function () {
            return 'hello';
        });
        $route2 = new Route(['POST'], '/foo/bar', function () {
            return 'hello';
        });

        $this->assertEmpty($router->getRoutes('OPTIONS'));
        $this->assertEquals(['/foo/bar' => $route1], $router->getRoutes('GET'));
        $this->assertEquals(['GET'  => ['/foo/bar' => $route1], 'POST' => ['/foo/bar' => $route2]], $router->getRoutes());
    }

    public function testDispatchCallableBasicUsage()
    {
        $router = new Router(new Container);
        $router->get('/foo', function () {
            return 'bar';
        });
        $response = $router->dispatch(Request::create('/foo'));

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('bar', $response->getContent());
    }

    public function testDispatchCallableWithClassTypeHintArguments()
    {
        $router = new Router(new Container);
        $router->get('/foo', function (ConcreteStub $concrete) {
            $this->assertInstanceOf(ConcreteStub::class, $concrete);
        });
        $response = $router->dispatch(Request::create('/foo'));

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    public function testDispatchCallableWithDefaultValues()
    {
        $router = new Router(new Container);
        $router->get('/foo', function ($id = 123, $options = []) {
            $this->assertSame(123, $id);
            $this->assertEmpty($options);
        });
        $response = $router->dispatch(Request::create('/foo'));

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    public function testDispatchCallableWithRouteParameters()
    {
        $router = new Router(new Container);
        $router->get('/foo/{path}/{id}', function ($path, $id) {
            $this->assertSame('bar', $path);
            $this->assertSame('123', $id);
        });

        $response = $router->dispatch(Request::create('/foo/bar/123'));

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEmpty($response->getContent());
    }

    public function testDispatchControllerBasicUsage()
    {
        $router = new Router(new Container);
        $router->get('/foo', '\\Melanth\\Test\Routing\\ControllerStub@get');
        $response = $router->dispatch(Request::create('/foo'));

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('foo', $response->getContent());
    }

    public function testDispatchControllerWithClassTypeForArguments()
    {
        $request = Request::create('/foo', 'GET', ['foo' => 'bar']);
        $container = new Container;
        $container->instance(Request::class, $request);

        $router = new Router($container);
        $router->get('/foo', '\\Melanth\\Test\\Routing\\ControllerWithInputParameterStub@get');
        $response = $router->dispatch($request);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('{"foo":"bar"}', $response->getContent());
    }

    public function testDispatchControllerWithDefaultValues()
    {
        $router = new Router(new Container);
        $router->get('/foo', '\\Melanth\\Test\\Routing\\ControllerWithDefaultValuesStub@get');
        $response = $router->dispatch(Request::create('/foo'));

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('{"foo":"bar"}', $response->getContent());
    }

    public function testDispatchControllerWithRouteParameters()
    {
        $router = new Router(new Container);
        $router->get('/path/{foo}', '\\Melanth\\Test\\Routing\\ControllerWithRouteParameterStub@get');
        $response = $router->dispatch(Request::create('/path/bar'));

        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('{"foo":"bar"}', $response->getContent());
    }

    public function testDispatchControllerWithArray()
    {
        $router = new Router(new Container);
        $router->get('foo/bar', ['uses' => '\\Melanth\\Test\\Routing\\ControllerStub@get']);
        $this->assertSame('foo', $router->dispatch(Request::create('foo/bar', 'GET'))->getContent());

        $router = new Router(new Container);
        $router->get('foo/bar', ['uses' => '\\Melanth\\Test\\Routing\\ControllerWithDefaultValuesStub@get']);
        $this->assertSame('{"foo":"bar"}', $router->dispatch(Request::create('foo/bar', 'GET'))->getContent());

        $router = new Router(new Container);
        $router->get('foo/{foo}', ['uses' => '\\Melanth\\Test\\Routing\\ControllerWithRouteParameterStub@get']);
        $this->assertSame('{"foo":"bar"}', $router->dispatch(Request::create('foo/bar', 'GET'))->getContent());
    }

    public function testDispatchRouteAndUrlNotMatch()
    {
        $router = new Router(new Container);

        $router->get('foo/bar', function () {
            return 'Hello World';
        });

        try {
            $router->dispatch(Request::create('foo'));
        } catch (NotFoundHttpException $e) {
            $this->assertInstanceof(NotFoundHttpException::class, $e);
            $this->assertSame(Response::HTTP_NOT_FOUND, $e->getStatusCode());
            $this->assertEmpty($e->getHeaders());
        }
    }

    public function testDispatchRouteAndMethodNotMatch()
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('');

        $router = new Router(new Container);
        $router->get('foo/bar', function () {
            return 'Hello World';
        });
        $router->dispatch(Request::create('foo/bar', 'POST'));
    }

    public function testDispatchCallableWithGroupAttributes()
    {
        $router = new Router(new Container);
        $router->group(['namespace' => '\\Melanth\\Test\\Routing'], function ($router) {
            $router->get('foo/bar', function () {
                return 'Hello World';
            });
        });
        $router->group(['prefix' => 'bar'], function ($router) {
            $router->get('foo', function () {
                return 'foo';
            });
        });

        $this->assertSame('Hello World', $router->dispatch(Request::create('foo/bar', 'GET'))->getContent());
        $this->assertSame('foo', $router->dispatch(Request::create('bar/foo', 'GET'))->getContent());
    }

    public function testDispatchControllerWithGroupAttributes()
    {
        $router = new Router(new Container);
        $router->group(['namespace' => '\\Melanth\\Test\\Routing'], function ($router) {
            $router->get('foo/bar', 'ControllerStub@get');
        });
        $router->group(['prefix' => 'bar'], function ($router) {
            $router->get('foo', '\\Melanth\\Test\\Routing\\ControllerStub@get');
        });

        $this->assertSame('foo', $router->dispatch(Request::create('foo/bar'))->getContent());
        $this->assertSame('foo', $router->dispatch(Request::create('bar/foo'))->getContent());
    }

    public function testSetGroupAttributesToRoute()
    {
        $action = [
            'uses' => '\\Melanth\\Test\\Routing\\ControllerStub@get',
            'controller' => '\\Melanth\\Test\\Routing\\ControllerStub@get',
            'domain' => 'mydomain.com'
        ];
        $router = new Router(new Container);
        $router->group(['domain' => 'mydomain.com'], function ($router) {
            return $router->get('foo/bar', '\\Melanth\\Test\\Routing\\ControllerStub@get');
        });
        $route = $router->findRoute(Request::create('foo/bar'), 'GET');
        $this->assertSame($action, $route->getAction());
    }

    public function testDispatchControllerWithInvokeMethod()
    {
        $router = new Router(new Container);
        $router->post('foo/bar', '\\Melanth\\Test\\Routing\\InvokableControllerStub');
        $this->assertSame('foo', $router->dispatch(Request::create('foo/bar', 'POST'))->getContent());
    }

    public function testDispatchControllerWithMissingAction()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Route has no action.');
        $router = neW Router(new Container);
        $router->get('foo/bar', null);
        $router->dispatch(Request::create('foo/bar'));
    }

    public function testDispatchControllerWithoutInvokeMethod()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Invalid route action \\Melanth\\Test\\Routing\\ControllerStub');
        $router = new Router(new Container);
        $router->get('foo/bar', '\\Melanth\\Test\\Routing\\ControllerStub');
        $router->dispatch(Request::create('foo/bar'));
    }

    public function testBindRouteAndLoadRouteFile()
    {
        $filesystem = $this->createMock(Filesystem::class);
        $filesystem->expects($this->once())
            ->method('require')
            ->with($this->equalTo(__DIR__.'/stubs/api.php'));

        $container = new Container;
        $container->bind('files', function () use ($filesystem) {
            return $filesystem;
        });

        $router = new Router($container);
        $this->assertNull($router->group([], __DIR__.'/stubs/api.php'));
    }

    public function testBindRoutesWithRouteAttributes()
    {
        $router = new Router(new Container);
        $router->namespace('\\Melanth\\Test\\Routing')->prefix('foo')->group(function ($router) {
            $router->get('bar', function () {
                return 'Hello World';
            });
        });

        $this->assertSame('Hello World', $router->dispatch(Request::create('foo/bar'))->getContent());
    }

    public function testBindCustomMethodWithUndefinedAttributes()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The attribute call does not exist.');
        $router = new Router(new Container);
        $router->call('foo');
    }

    public function testBindCustomMethodAndCallUnrecognizedMethod()
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Method call does not exist.');
        $router = new Router(new Container);
        $router->prefix('foo')->call();
    }

    public function testDispatchCallableWithDefaultClass()
    {
        $router = new Router(new Container);
        $router->get('foo/bar', function (ConcreteStub $stub = null) {
            return $stub;
        });

        $this->assertEmpty($router->dispatch(Request::create('foo/bar'))->getContent());
    }

    public function testDispatchCallabbleWithoutEmptyParameters()
    {
        $router = new Router(new Container);
        $route = $router->get('foo/bar', function (ConcreteStub $stub, $foo) {
            return get_class($stub).'|'.$foo;
        });

        $route->setParameter(ConcreteStub::class, new ConcreteStub)->setParameter('foo', 'bar');
        $this->assertInstanceOf(Response::class, $router->dispatch(Request::create('foo/bar', 'GET')));
    }

    public function testDispatchControllerWithDefaultClassStub()
    {
        $router = new Router(new Container);
        $router->get('foo/bar', '\\Melanth\\Test\\Routing\\ControllerWithDefaultClassStub@get');
        $this->assertEmpty($router->dispatch(Request::create('foo/bar'))->getContent());
    }

    public function testDispatchControllerWithExistingInstance()
    {
        $request = Request::create('foo/bar');
        $router = new Router(new Container);
        $route = $router->get('foo/bar', '\\Melanth\\Test\\Routing\\ControllerWithExistingParametersStub@get');
        $route->setParameter(ConcreteStub::class, new ConcreteStub)->setParameter('foo', 'bar');
        $this->assertSame('Melanth\Test\Routing\ConcreteStub|bar', $router->dispatch($request)->getContent());
    }
}

class ControllerStub
{
    public function get()
    {
        return 'foo';
    }
}

class ControllerWithInputParameterStub
{
    public function get(Request $request)
    {
        return $request->all();
    }
}

class ControllerWithDefaultValuesStub
{
    public function get($foo = 'bar')
    {
        return compact('foo');
    }
}

class ControllerWithRouteParameterStub
{
    public function get($foo)
    {
        return compact('foo');
    }
}

class ControllerWithDefaultClassStub
{
    public function get(ConcreteStub $stub = null)
    {
        return $stub;
    }
}

class ControllerWithExistingParametersStub
{
    public function get(ConcreteStub $stub, string $foo)
    {
        return get_class($stub).'|'.$foo;
    }
}

class InvokableControllerStub
{
    public function __invoke()
    {
        return 'foo';
    }
}

class ConcreteStub
{
    //
}

class Filesystem
{
    public function require()
    {
        //
    }
}
