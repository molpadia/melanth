<?php

namespace Melanth\Tests\Http;

use JsonSerializable;
use Melanth\Contracts\Support\Arrayable;
use Melanth\Contracts\Support\Jsonable;
use Melanth\Http\Exceptions\HttpResponseException;
use Melanth\Http\Request;
use Melanth\Http\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    public static $headerAlreadySent = false;
    public static $expectedMethod;

    public function testMakeResponseWithDefaultValue()
    {
        $response = new Response;
        $this->assertSame('', $response->content());
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEmpty($response->headers()->all());
    }

    public function testMakeResponseBasicUsage()
    {
        $response = new Response('foo');
        $this->assertSame('foo', $response->getContent());
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertEmpty($response->headers()->all());
    }

    public function testMakeResponseWithBadRequestStatus()
    {
        $response = new Response(null, 400);
        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
    }

    public function testMakeResponseWithHeaders()
    {
        $response = new Response;
        $response->withHeader(['Content-Type' => 'application/json']);
        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers()->get('Content-Type'));
    }

    public function testMakeGlobablResponse()
    {
        $response = Response::create('foo', 200, ['Content-Type' => 'application/json']);
        $this->assertInstanceOf(Response::class, $response);
        $this->assertSame('foo', $response->getContent());
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('application/json', $response->headers()->get('Content-Type'));
    }

    public function testPrepareRequestBasicUsage()
    {
        $request = Request::create('/foo');
        $response = Response::create('foo', 200, ['CONTENT_TYPE' => 'application/json'])->prepare($request);
        $this->assertSame('1.1', $response->getVersion());
        $this->assertSame('foo', $response->getContent());
        $this->assertSame('application/json', $response->headers()->get('Content-Type'));
    }

    public function testPrepareRequestWithDefaultServerProtocol()
    {
        $request = Request::create('/foo', 'GET', [], ['SERVER_PROTOCOL' => 'HTTP/1.0']);
        $response = Response::create('foo')->prepare($request);
        $this->assertSame('1.0', $response->getVersion());
        $this->assertSame('foo', $response->getContent());
        $this->assertSame(Response::HTTP_OK, $response->status());
        $this->assertNull($response->headers()->get('CONTENT_TYPE'));
    }

    public function testPrepareRequestWithHeadMethod()
    {
        $request = Request::create('/foo', 'HEAD');
        $response = Response::create('foo', 200, ['CONTENT_TYPE' => 'application/json'])->prepare($request);
        $this->assertSame('1.1', $response->getVersion());
        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_OK, $response->status());
        $this->assertSame('application/json', $response->headers()->get('CONTENT_TYPE'));
    }

    public function testPreparRequestWithInformationStatus()
    {
        $headers = ['CONTENT_TYPE' => 'application/json', 'CONTENT_LENGTH' => 3];
        $request = Request::create('/foo');
        $response = Response::create('foo', Response::HTTP_CONTINUE, $headers)->prepare($request);
        $this->assertSame('1.1', $response->getVersion());
        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_CONTINUE, $response->status());
        $this->assertNull($response->headers()->get('CONTENT_TYPE'));
        $this->assertNull($response->headers()->get('CONTENT_LENGTH'));
    }

    public function testPrepareRequestWithEmptyStatus()
    {
        $headers = ['Content-Type' => 'application/json', 'Content-Length' => 3];
        $request = Request::create('/foo');
        $response = Response::create('foo', Response::HTTP_NO_CONTENT, $headers)->prepare($request);
        $this->assertSame('1.1', $response->getVersion());
        $this->assertSame('', $response->getContent());
        $this->assertSame(Response::HTTP_NO_CONTENT, $response->status());
        $this->assertNull($response->headers()->get('Content-Type'));
        $this->assertNull($response->headers()->get('Content-Length'));
    }

    public function testPrepareRequestWithTextContentType()
    {
        $headers = ['CONTENT_TYPE' => 'text/html'];
        $request = Request::create('/foo');
        $response = Response::create('foo', Response::HTTP_OK, $headers)->prepare($request);
        $this->assertSame('1.1', $response->getVersion());
        $this->assertSame('foo', $response->getContent());
        $this->assertSame('text/html;charset=UTF-8', $response->headers()->get('Content-Type'));

        $request = Request::create('foo');
        $response = new Response(null, Response::HTTP_OK, ['Content-Type' => 'text/html']);
        $response->setCharset('ISO-8859-1')->prepare($request);
        $this->assertSame('text/html;charset=ISO-8859-1', $response->headers()->get('Content-Type'));
    }

    public function testSendResponseOutput()
    {
        $response = new Response;
        $this->assertInstanceOf(Response::class, $response->withHeader(['Content-Type' => 'text/html'])->send());

        ob_start();
        $response = Response::create('foo')->send();
        $this->assertSame('foo', ob_get_clean());
    }

    public function testSendOutputWithFastcgiRequest()
    {
        ResponseTest::$expectedMethod = 'fastcgi_finish_request';
        $this->assertInstanceOf(Response::class, Response::create()->send());
    }

    public function testSendOutputAndHeadersAlreadyBeenSent()
    {
        ResponseTest::$headerAlreadySent = true;
        $this->assertInstanceOf(Response::class, Response::create()->send());
    }

    public function testSendOutputWithOtherInterfaceSource()
    {
        // runkit_constant_redefine('PHP_SAPI', 'fpm-fcgi');
        $this->assertInstanceOf(Response::class, Response::create()->send());
    }

    public function testConvertedJsonResponse()
    {
        $response = new Response('foo');
        $this->assertSame('foo', $response->getContent());
        $this->assertNull($response->headers()->get('Content-Type'));

        $response = new Response(['foo' => 'bar']);
        $this->assertSame('{"foo":"bar"}', $response->getContent());
        $this->assertSame('application/json', $response->headers()->get('Content-Type'));

        $response = new Response(new ArrayableStub);
        $this->assertSame('{"foo":"bar"}', $response->getContent());
        $this->assertSame('application/json', $response->headers()->get('Content-Type'));

        $response = new Response(new JsonableStub);
        $this->assertSame('{"foo":"bar"}', $response->getContent());
        $this->assertSame('application/json', $response->headers()->get('Content-Type'));

        $response = new Response(new JsonSerializableStub);
        $this->assertSame('{"foo":"bar"}', $response->getContent());
        $this->assertSame('application/json', $response->headers()->get('Content-Type'));

        $response = new Response(new ArrayableAndJsonStub);
        $this->assertSame('{"foo":"bar"}', $response->getContent());
        $this->assertSame('application/json', $response->headers()->get('Content-Type'));
    }

    public function testResponseStatusCode()
    {
        $this->assertTrue((new Response(null, 100))->isInformation());
        $this->assertTrue((new Response(null, 199))->isInformation());
        $this->assertFalse((new Response(null, 200))->isInformation());
        $this->assertTrue((new Response(null, 204))->isEmpty());
        $this->assertTrue((new Response(null, 304))->isEmpty());
        $this->assertFalse((new Response(null, 200))->isEmpty());
        $this->assertTrue((new Response(null, 304))->isEmpty());
        $this->assertTrue((new Response(null, 201))->isRedirect());
        $this->assertTrue((new Response(null, 301))->isRedirect());
        $this->assertTrue((new Response(null, 302))->isRedirect());
        $this->assertTrue((new Response(null, 303))->isRedirect());
        $this->assertTrue((new Response(null, 307))->isRedirect());
        $this->assertTrue((new Response(null, 308))->isRedirect());
    }

    public function testThrowResponseException()
    {
        $response = new Response;

        try {
            $response->throwException();
        } catch (HttpResponseException $e) {
            $this->assertInstanceOf(Response::class, $e->getResponse());
            $this->assertSame($response, $e->getResponse());
        }
    }

    public function testConvertResponseToString()
    {
        $results = ['HTTP/1.0 200 OK', 'Content-Type: application/json', 'foo'];
        $response = new Response('foo', 200, ['Content-Type' => 'application/json']);
        $this->assertSame(implode("\r\n", $results), (string) $response);
    }
}

class ArrayableStub implements Arrayable
{
    public function toArray() : array
    {
        return ['foo' => 'bar'];
    }
}

class JsonableStub implements Jsonable
{
    public function toJson($options = 0) : string
    {
        return '{"foo":"bar"}';
    }
}

class JsonSerializableStub implements JsonSerializable
{
    public function jsonSerialize()
    {
        return ['foo' => 'bar'];
    }
}

class ArrayableAndJsonStub implements Arrayable, Jsonable
{
    public function toJson($options = 0) : string
    {
        return '{"foo":"bar"}';
    }

    public function toArray() : array
    {
        return [];
    }
}

namespace Melanth\Http;

function headers_sent() {
    return \Melanth\Tests\Http\ResponseTest::$headerAlreadySent;
}

function header() {
    //
}

function function_exists($method) {
    return \Melanth\Tests\Http\ResponseTest::$expectedMethod === $method;
}

function fastcgi_finish_request() {
    //
}
