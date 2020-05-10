<?php

namespace Melanth\Test;

use InvalidArgumentException;
use Melanth\Http\Request;
use Melanth\Http\Headers;
use Melanth\Support\Collection;
use PHPUnit\Framework\TestCase;

class RequestTest extends TestCase
{
    private $tempDir;

    public function setUp() : void
    {
        parent::setUp();

        $this->tempDir = __DIR__.'/tmp';

        @mkdir($this->tempDir);
    }

    public function tearDown() : void
    {
        @rmdir($this->tempDir);

        parent::tearDown();
    }

    public function testCreateRequestBasicUsage()
    {
        $request = Request::create('http://www.example.com/path/foo');

        $this->assertSame('GET', $request->getMethod());
        $this->assertSame('http', $request->getScheme());
        $this->assertSame('www.example.com', $request->getHost());
        $this->assertSame(Request::HTTP_PORT, $request->getPort());
        $this->assertSame('http://www.example.com/path/foo', $request->getUri());
        $this->assertSame('/path/foo', $request->path());
        $this->assertEmpty($request->getQueryString());
        $this->assertNull($request->getServerConfig()->get('CONTENT_TYPE'));
    }

    public function testCreateRequestWithQuery()
    {
        $request = Request::create('http://www.example.com/foo/boo?foo=bar&bar=foo');

        $this->assertSame('http://www.example.com/foo/boo?foo=bar&bar=foo', $request->getUri());
        $this->assertSame('/foo/boo', $request->path());
        $this->assertSame('bar=foo&foo=bar', $request->getQueryString());
        $this->assertTrue(isset($request->foo));
        $this->assertSame('bar', $request->foo);
    }

    public function testCreateRequestWithHash()
    {
        $request = Request::create('htpps://www.example.com/path#foo', 'GET', [], [], ['REQUEST_URI' => 'htpps://www.example.com/path#foo']);

        $this->assertSame('http://www.example.com/path', $request->getUri());
    }

    public function testCreateRequestScheme()
    {
        $request = Request::create('http://www.example.com/foo');

        $this->assertFalse($request->isSecure());
        $this->assertSame('http', $request->getScheme());
        $this->assertSame(Request::HTTP_PORT, $request->getPort());
    }

    public function testCreateRequestWithSecureScheme()
    {
        $request = Request::create('https://www.example.com/foo');

        $this->assertTrue($request->isSecure());
        $this->assertSame('https', $request->getScheme());
        $this->assertSame('on', $request->getServerConfig()->get('HTTPS'));
        $this->assertSame(Request::HTTPS_PORT, $request->getPort());
    }

    public function testCreateRequestWithCustomPort()
    {
        $request = Request::create('https://www.example.com:8000/foo', 'GET', [], ['HTTP_HOST' => 'www.example.com:8000']);

        $this->assertSame('https://www.example.com:8000', $request->getSchemeAndHost());
        $this->assertSame(8000, $request->getPort());
    }

    public function testCreateRequestWithDefaultHttpPort()
    {
        $request = Request::create('https://www.example.com', 'GET', [], ['HTTP_HOST' => 'www.example.com']);

        $this->assertSame(Request::HTTPS_PORT, $request->getPort());
    }

    public function testCreateRequestWithHttpMethod()
    {
        $request = Request::create('https://www.example.com:8000/foo', 'POST');

        $this->assertTrue($request->isMethod('POST'));
        $this->assertSame('POST', $request->getMethod());
        $this->assertSame('application/x-www-form-urlencoded', $request->getServerConfig()->get('CONTENT_TYPE'));
    }

    public function testCreateRequestWithUsernameAndPassword()
    {
        $request = Request::create('http://username:password@hostname:9090/path');

        $this->assertSame('username', $request->getServerConfig()->get('PHP_AUTH_USER'));
        $this->assertSame('password', $request->getServerConfig()->get('PHP_AUTH_PW'));
        $this->assertSame('http://hostname:9090/path', $request->getUri());
    }

    public function testCreateRequestWithPostMethod()
    {
        $request = Request::create('/path/foo/bar', 'POST');
        $this->assertSame('POST', $request->getMethod());
    }

    public function testCreateRequestAndMergeWithQueryParameters()
    {
        $request = Request::create('/path?foo=bar#test', 'GET', ['bar' => 'foo']);

        $this->assertSame('foo=bar', $request->getQueryString());
        $this->assertEquals(['foo' => 'bar', 'bar' => 'foo'], $request->getInputSource()->all());
        $this->assertEquals(['foo' => 'bar', 'bar' => 'foo'], $request->all());
        $this->assertEquals(['foo' => 'bar', 'bar' => 'foo'], $request->toArray());
    }

    public function testCreateRequestAndMergeWithMessageBody()
    {
        $request = Request::create('/path?foo=bar', 'POST', ['bar' => 'foo']);

        $this->assertSame('foo=bar', $request->getQueryString());
        $this->assertEquals(['bar' => 'foo'], $request->getInputSource()->all());
        $this->assertEquals(['foo' => 'bar', 'bar' => 'foo'], $request->all());
        $this->assertEquals(['foo' => 'bar', 'bar' => 'foo'], $request->toArray());
    }

    public function testCreateRequestAndGetServerConfig()
    {
        $request = Request::create('http://username:password@hostname:9090/path?arg=value#anchor');
        $request->getHeaders()->remove('HOST');

        $this->assertSame('127.0.0.1', $request->ip());
        $this->assertSame('http', $request->getScheme());
        $this->assertSame('hostname:9090', $request->getHost());
        $this->assertSame(9090, $request->getPort());
        $this->assertSame('http://hostname:9090/path?arg=value', $request->fullUrl());
        $this->assertSame('/path', $request->path());
        $this->assertSame('Melanth', $request->userAgent());
    }

    public function testCreateRequestAndConvertString()
    {
        $request = Request::create('http://username:password@hostname:9090/path?arg=value#anchor');

        $this->assertIsString((string) $request);
    }

    public function testGetDecodedPathSegments()
    {
        $request = Request::create('/foo/boo//melanth');

        $this->assertEquals(['foo', 'boo', 'melanth'], $request->segments());
    }

    public function testGetHostWithFakeDomainThrowError()
    {
        $this->expectException(InvalidArgumentException::class);

        $request = Request::create('', 'GET', [], ['HTTP_HOST' => 'www.host.com?query=string']);
        $request->getHost();
    }

    public function testCreateRequestAndGetContent()
    {
        $request = Request::create('path', 'GET', [], [], [], [], 'foo');

        $this->assertSame('foo', $request->getContent());
    }

    public function testGetContentFromResource()
    {
        $filename = $this->tempDir.'/test.txt';
        $file = fopen($filename, 'w+');
        fwrite($file, 'foo=bar');

        $request = Request::create('/path', 'GET', [], [], [], [], $file);

        @unlink($filename);

        $this->assertSame('foo=bar', $request->getContent());
    }

    public function testCaptureRequestFromGlobals()
    {
        $this->assertInstanceOf(Request::class, Request::capture());
    }

    public function testCreateRequestAndGetHeaders()
    {
        $request = Request::create('path', 'GET', [], ['HTTP_MELANTH' => 'framework']);
        $headers = $request->getHeaders();

        $this->assertInstanceOf(Headers::class, $headers);
        $this->assertSame('framework', $headers['MELANTH']);

        $headers->set('HTTP-MELANTH-LANGUAGE', 'PHP');
        $this->assertSame('PHP', $headers->get('HTTP-MELANTH-LANGUAGE'));
    }

    public function testCreateRequestFromGlobalsWithUnsupportedHttpMethod()
    {
        $_SERVER += ['REQUEST_METHOD' => 'PUT', 'CONTENT_TYPE' => 'application/x-www-form-urlencoded'];
        $request = RequestProxy::createFromGlobals();

        $this->assertEquals(['_method' => 'PUT', 'content' => 'foo'], $request->getInputSource()->all());
    }

    public function testGetRequestCookies()
    {
        $request = Request::create('http://www.example.com/path/foo');

        $this->assertEmpty($request->getHeaders()->getCookies());
    }

    public function testGetUrlWithoutQueryString()
    {
        $request = Request::create('http://hostname:9090/path?arg=value#anchor');

        $this->assertSame('http://hostname:9090/path', $request->url());
    }

    public function testGetDecodedPathInfo()
    {
        $request = Request::create('https://hostanme:9090/foo%20bar%40baz');

        $this->assertSame('/foo bar@baz', $request->decodedPath());
    }

    public function testCreateRequestWithArrayAccess()
    {
        $request = Request::create('/path/foo');
        $request['foo'] = 'bar';

        $this->assertTrue(isset($request['foo']));
        $this->assertFalse(isset($request['bar']));
        $this->assertSame('bar', $request['foo']);

        unset($request['foo']);
        $this->assertFalse(isset($request['foo']));
    }

    public function testIfPathInfoIsExist()
    {
        $request = Request::create('/path/foo?boo=bar');

        $this->assertTrue($request->is('foo'));
        $this->assertFalse($request->is('boo'));
        $this->assertFalse($request->is('bar'));
    }

    public function testGetContentBodyWithJson()
    {
        $content = '{"id":12345,"foo":"bar"}';
        $request = Request::create('/path', 'POST', [], [], [], [], $content);

        $this->assertSame(['id' => 12345, 'foo' => 'bar'], $request->json());
        $this->assertSame('bar', $request->json('foo'));
        $this->assertNull($request->json('boo'));
    }

    public function testGetInputSourceWithHeadMethod()
    {
        $query = new Collection;
        $query->set('foo', 'bar');
        $query->set('bar', 'foo');

        $request = Request::create('/path?foo=bar', 'HEAD', ['bar' => 'foo']);

        $this->assertTrue($request->isMethod('HEAD'));
        $this->assertEquals($query, $request->getInputSource());
    }

    public function testCreateRequestWithAjax()
    {
        $config = [
            'HTTP_X_Requested_With' => 'XMLHttpRequest',
            'CONTENT_TYPE' => 'application/json'
        ];

        $request = Request::create('/path', 'GET', [], $config);

        $this->assertTrue($request->isJson());
        $this->assertTrue($request->isXMLHttpRequest());
        $this->assertTrue($request->ajax());
        $this->assertSame('XMLHttpRequest', $request->getHeaders()->get('X-Requested-With'));
    }

    public function testRequestIsPAjax()
    {
        $request = Request::create('/path', 'GET', [], ['HTTP_X_PJAX' => true]);

        $this->assertTrue($request->pajax());
    }
}

class RequestProxy extends Request
{
    public function getContent()
    {
        return http_build_query(['_method' => 'PUT', 'content' => 'foo'], '', '&');
    }
}
