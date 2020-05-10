<?php

namespace Melanth\Routing;

use Melanth\Http\Request;
use Melanth\Routing\Route;
use PHPUnit\Framework\TestCase;

class RouteTest extends TestCase
{
    public function testBindRouteBasicUsage()
    {
        $closure = function () {
            //
        };

        $route = new Route(['GET'], '/foo/bar', $closure);
        $this->assertSame(['GET'], $route->methods());
        $this->assertSame('/foo/bar', $route->uri());
        $this->assertSame(['uses' => $closure], $route->getAction());

        $route = new Route(['POST'], '/foo/bar', ['uses' => __DIR__.'/ControllerStub@post']);
        $this->assertSame(['POST'], $route->methods());
        $this->assertSame('/foo/bar', $route->uri());
        $this->assertSame(['uses' => __DIR__.'/ControllerStub@post'], $route->getAction());
    }

    public function testBindRequestBasicUsage()
    {
        $closure = function () {
            //
        };

        $route = new Route(['GET'], 'foo/bar', $closure);
        $this->assertEmpty($route->bind(Request::create('api'))->parameters());

        $route = new Route(['GET'], '/api/{foo}', $closure);
        $this->assertSame(['foo' => 'bar'], $route->bind(Request::create('api/bar'))->parameters());

        $route = new Route(['GET'], 'api/foo', $closure);
        $route->bind(Request::create('api/foo'))->setParameter('foo', 'bar');
        $this->assertSame(['foo' => 'bar'], $route->parameters());

        $route = new Route(['GET'], 'api/{foo}', $closure);
        $route->bind(Request::create('api/bar'))->setParameter('foo', 'boo');
        $this->assertSame(['foo' => 'boo'], $route->parameters());
    }


    public function testGetDomainBasicUsage()
    {
        $closure = function () {
            //
        };

        $route = new Route(['GET'], 'foo/bar', $closure);
        $this->assertNull($route->domain());
        $this->assertSame('mydomain.com', $route->setDomain('mydomain.com')->domain());
    }

    public function testGetRegexRoute()
    {
        $closure = function () {
            //
        };

        $route = new Route(['GET'], 'foo/bar', $closure);
        $this->assertSame('#^foo/bar$#u', $route->getRegex());

        $route = new Route(['GET'], 'foo/bar/?id={id}#hash', $closure);
        $this->assertSame('#^foo/bar/?id=[^/]+#hash$#u', $route->getRegex());
    }

    public function testAccessUri()
    {
        $closure = function () {
            //
        };

        $route = new Route(['GET'], '', $closure);
        $route->setUri('');
        $this->assertSame(['GET'], $route->methods());
        $this->assertSame('/', $route->uri());
        $this->assertSame(['uses' => $closure], $route->getAction());
    }
}
