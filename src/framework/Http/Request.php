<?php

namespace Melanth\Http;

use ArrayAccess;
use InvalidArgumentException;
use Melanth\Contracts\Http\Request as RequestContract;
use Melanth\Support\Arr;
use Melanth\Support\Collection;

class Request implements ArrayAccess, RequestContract
{
    use Concerns\HasInput,
        Concerns\HasContentTypes;

    /**
     * The server port number in HTTP protocol.
     *
     * @var int
     */
    public const HTTP_PORT = 80;

    /**
     * The secure server port number in HTTP protocol.
     *
     * @var int
     */
    public const HTTPS_PORT = 443;

    /**
     * The accepted formats for web browsers and it's defined and standardized in IETF's RFC 6838.
     *
     * @var array
     */
    public const MIMETYPES = [
        'html' =>   ['text/html', 'application/xhtml+xml'],
        'txt' =>    ['text/plain'],
        'js' =>     ['application/javascript', 'application/x-javascript', 'text/javascript'],
        'css' =>    ['text/css'],
        'json' =>   ['application/json', 'application/x-json'],
        'jsonld' => ['application/ld+json'],
        'xml' =>    ['text/xml', 'application/xml', 'application/x-xml'],
        'rdf' =>    ['application/rdf+xml'],
        'atom' =>   ['application/atom+xml'],
        'rss' =>    ['application/rss+xml'],
        'form' =>   ['application/x-www-form-urlencoded'],
    ];

    /**
     * The query parameter collection.
     *
     * @var \Melanth\Support\Collection
     */
    protected $query;

    /**
     * The data of message body with the request.
     *
     * @var \Melanth\Support\Collection
     */
    protected $body;

    /**
     * The cookie store to retrieve state information on client.
     *
     * @var array
     */
    protected $cookies;

    /**
     * The uploaded file parameters.
     *
     * @var \Melanth\Http\Collection
     */
    protected $files;

    /**
     * The server configuration parameters.
     *
     * @var \Melanth\Http\ServerConfig
     */
    protected $serverConfig;

    /**
     * The headers extracted from the server configuration.
     *
     * @var \Melanth\Http\Headers
     */
    protected $headers;

    /**
     * The raw body data wrapeed by the resource.
     *
     * @var string|resource|null|bool
     */
    protected $content;

    /**
     * The incoming request message body.
     *
     * @var array
     */
    protected $json;

    /**
     * Create a new request instance.
     *
     * @param array                $query        The query parameters attached to URL.
     * @param array                $body         The message body of data.
     * @param array                $serverConfig The HTTP server configuration.
     * @param array                $cookies      The cookie key value pair.
     * @param array                $files        The file key value pair.
     * @param string|resource|null $content      The content used to store request body.
     *
     * @return void
     */
    public function __construct(array $query, array $body, array $serverConfig, array $cookies, array $files, $content = null)
    {
        $this->query = new Collection($query);
        $this->body = new Collection($body);
        $this->serverConfig = new Collection($serverConfig);
        $this->cookies = new Collection($cookies);
        $this->files = new Collection($files);
        $this->content = $content;
        $this->headers = new Headers($this->extractServerHeaders());
    }

    /**
     * Create an instance directly.
     *
     * @param string               $url          The uniform resource identifier.
     * @param string               $method       The HTTP method.
     * @param array                $body         The request message body.
     * @param array                $serverConfig The HTTP server configuration.
     * @param array                $cookies      The cookie key value pair.
     * @param array                $files        The file key value pair.
     * @param string|resource|null $content      The content used to store request body.
     *
     * @return $this
     */
    public static function create(
        string $url, string $method = 'GET', array $parameters = [], array $serverConfig = [], array $cookies = [], array $files = [], $content = null
    ) : Request {
        $method = strtoupper($method);
        $parser = new UrlParser($url);
        [$query, $body] = static::parseQueryAndBody($method, $parameters);

        $query += $parser->parseQuery();
        $serverConfig += $parser->parseConfig();

        $serverConfig['REQUEST_METHOD'] = $method;

        if (! isset($serverConfig['CONTENT_TYPE']) && in_array($method, ['POST', 'PUT', 'DELETE'])) {
            $serverConfig['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
        }

        return new static($query, $body, $serverConfig, $cookies, $files, $content);
    }

    /**
     * Parse query parameters and message body by given HTTP method.
     *
     * @param string $method     The HTTP method.
     * @param array  $parameters The data either message body or query parameters.
     *
     * @return array
     */
    protected static function parseQueryAndBody(string $method, array $parameters = []) : array
    {
        $query = [];

        if (! in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $query = $parameters;
            $parameters = [];
        }

        return [$query, $parameters];
    }

    /**
     * Create a request from global HTTP server.
     *
     * @return $this
     */
    public static function createFromGlobals() : Request
    {
        $request = new static($_GET, $_POST, $_SERVER, $_COOKIE, $_FILES);

        if ($request->isBrowserUnsupportedMethod()) {
            parse_str($request->getContent(), $body);

            $request->body = new Collection($body);
        }

        return $request;
    }

    /**
     * Get a new HTTP request instance from server.
     *
     * @return $this
     */
    public static function capture() : Request
    {
        return static::createFromGlobals();
    }

    /**
     * Extract HTTP headers from server configuration.
     *
     * @return array
     */
    protected function extractServerHeaders() : array
    {
        $headers = [];

        foreach ($this->serverConfig as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headers[substr($key, 5)] = $value;
            }
            // Entity headers also contain information about the body of the resource,
            // like content type, content length etc.
            elseif (in_array($key, ['CONTENT_LENGTH', 'CONTENT_MD5', 'CONTENT_TYPE'])) {
                $headers[$key] = $value;
            }
        }

        return $headers;
    }

    /**
     * Get the message body with the request.
     *
     * @return resource
     */
    public function getContent()
    {
        if (is_resource($this->content)) {
            rewind($this->content);
            return stream_get_contents($this->content);
        }

        if (! $this->content) {
            $this->content = fopen('php://input', 'rb');
        }

        return $this->content;
    }

    /**
     * Determine whetehr the given method is identical.
     *
     * @param string $method The HTTP method.
     *
     * @return bool
     */
    public function isMethod(string $method) : bool
    {
        return strtoupper($method) === $this->getMethod();
    }

    /**
     * Get the method with the request.
     *
     * @return string
     */
    public function getMethod() : string
    {
        return $this->serverConfig->get('REQUEST_METHOD', 'GET');
    }

    /**
     * Get the HTTP request method.
     *
     * @return string
     */
    public function method() : string
    {
        return $this->getMethod();
    }

    /**
     * Get the HTTP scheme from the request.
     *
     * @return string
     */
    public function getScheme() : string
    {
        return $this->isSecure() ? 'https' : 'http';
    }

    /**
     * Determine whether the host scheme is secure.
     *
     * @return bool
     */
    public function isSecure() : bool
    {
        return isset($this->serverConfig['HTTPS']) && $this->serverConfig['HTTPS'] === 'on';
    }

    /**
     * Get the IP address from the server configuration.
     *
     * @return string
     */
    public function ip() : string
    {
        return $this->serverConfig->get('REMOTE_ADDR');
    }

    /**
     * Get the hostname with the request.
     *
     * @return string
     */
    public function getHost() : string
    {
        return $this->sanitiseHost($this->getConfigHost());
    }

    /**
     * Sanitise the hostnaem from the config,
     * we remove the port number followed by RFC 952/2181.
     * Since HTTP_HOST and SERVER_NAME may come from client ,
     * we need to check the hostname contain malicious characters.
     * In addition, use preg_replace instead of preg_match to prevent DDos atacks with long hostname.
     *
     * @param string $host The hostnaem from server configuration.
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function sanitiseHost(string $host) : string
    {
        $host = strtolower(trim($host));

        if (! $host || preg_replace('/(?:^\[)?[a-zA-Z0-9-:\]_]+\.?/', '', $host) !== '') {
            throw new InvalidArgumentException("Invalid hostname {$host}.");
        }

        return $host;
    }

    /**
     * Get the host name with the request.
     *
     * @return string
     */
    protected function getConfigHost() : string
    {
        return $this->headers->get('HOST') ??
            $this->serverConfig->get('HTTP_HOST') ?? $this->serverConfig->get(['SERVER_ADDR'], '');
    }

    /**
     * Get the port number with the request.
     *
     * @return int
     */
    public function getPort() : int
    {
        if (! $this->headers->has('HOST')) {
            return $this->serverConfig->get('SERVER_PORT');
        }

        if (($position = strpos($this->headers->get('HOST'), ':')) !== false) {
            return substr($this->headers->get('HOST'), $position + 1);
        }

        return $this->getScheme() === 'https' ? self::HTTPS_PORT : Request::HTTP_PORT;
    }

    /**
     * Get the HTTP scheme and hostname.
     *
     * @return string
     */
    public function getSchemeAndHost() : string
    {
        return $this->getScheme().'://'.$this->getHost();
    }

    /**
     * Get the input source with the request.
     *
     * @return \Melanth\Support\Collection
     */
    public function getInputSource() : Collection
    {
        return in_array($this->method(), ['GET', 'HEAD']) ? $this->query : $this->body;
    }

    /**
     * Get the query string with the request.
     *
     * @return string
     */
    public function getQueryString() : string
    {
        return $this->normalizeQuery($this->serverConfig->get('QUERY_STRING'));
    }

    /**
     * Normalize query string.
     *
     * @param string|null $queryString The query string with the request.
     *
     * @return string
     */
    protected function normalizeQuery(string $queryString = null) : string
    {
        if (! $queryString) {
            return '';
        }

        parse_str($queryString, $parameters);
        ksort($parameters);

        return http_build_query($parameters, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * Get the url without query string from the request.
     *
     * @return string
     */
    public function url() : string
    {
        return rtrim(preg_replace('/\?.*/', '', $this->getUri()), '/');
    }

    /**
     * Get the full URL with the request.
     *
     * @return string
     */
    public function fullUrl() : string
    {
        return $this->getUri();
    }

    /**
     * Get HTTP URI with the request.
     *
     * @return string
     */
    public function getUri() : ?string
    {
        return $this->getSchemeAndHost().'/'.ltrim($this->serverConfig['REQUEST_URI'], '/');
    }

    /**
     * Get the path segments for the request.
     *
     * @return array
     */
    public function segments()
    {
        $values = [];
        $segments = explode('/', $this->decodedPath());

        foreach ($segments as $segment) {
            if ($segment !== '') {
                $values[] = $segment;
            }
        }

        return $values;
    }

    /**
     * Get the json payload for the request.
     *
     * @param string|null $key     The given key.
     * @param string|null $default The default value.
     *
     * @return mixed
     */
    public function json(string $key = null, string $default = null)
    {
        if (! isset($this->json)) {
            $this->json = (array) json_decode($this->getContent(), true);
        }

        return is_null($key) ? $this->json : data_get($this->json, $key, $default);
    }

    /**
     * Determine whether the current request URI matches a pattern
     *
     * @param mixed $patterns The sequence list of the patterns.
     *
     * @return bool
     */
    public function is($patterns) : bool
    {
        $path = $this->decodedPath();
        $patterns = is_array($patterns) ? $patterns : func_get_args();

        foreach ($patterns as $pattern) {
            if (strpos($path, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the request method is unsupported in browser.
     *
     * @return bool
     */
    public function isBrowserUnsupportedMethod() : bool
    {
        return in_array($this->getMethod(), ['PUT', 'PATCH', 'DELETE']) &&
            strpos($this->headers->get('Content-Type'), 'application/x-www-form-urlencoded') !== false;
    }

    /**
     * Get the current path info with the request.
     *
     * @return string
     */
    public function path() : string
    {
        return parse_url($this->getUri(), PHP_URL_PATH);
    }

    /**
     * Get a decoded path.
     *
     * @return string
     */
    public function decodedPath() : string
    {
        return rawurldecode($this->path());
    }

    /**
     * Get the HTTP message body with the request.
     *
     * @return string
     */
    protected function getMessageBody() : string
    {
        return sprintf(
            "%s\r\n%s\r\n%s", $this->serverConfig['SERVER_PROTOCOL'], $this->headers, $this->getContent()
        );
    }

    /**
     * Get the client user agent.
     *
     * @return string
     */
    public function userAgent() : string
    {
        return $this->headers->get('User-Agent');
    }

    /**
     * Get all of the input and filed for th3e reuqest.
     *
     * @return array
     */
    public function toArray() : array
    {
        return $this->all();
    }

    /**
     * Get server configuration from the request.
     *
     * @return \Melanth\Http\Collection
     */
    public function getServerConfig() : Collection
    {
        return $this->serverConfig;
    }

    /**
     * Get the HTTP headers from the request.
     *
     * @return \Melanth\Http\Headers
     */
    public function getHeaders() : Headers
    {
        return $this->headers;
    }

    /**
     * Set the value at a given offset.
     *
     * @param string $key  The given key name.
     * @param mixed $value The given value.
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->getInputSource()->set($key, $value);
    }

    /**
     * Get the value from the offset.
     *
     * @param  string  $key
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->__get($key);
    }

    /**
     * Determine whether the given offset exists.
     *
     * @param string $offset The given offset.
     * @return bool
     */
    public function offsetExists($offset)
    {
        return Arr::has($this->all(), $offset);
    }

    /**
     * Remove the value from the given input source.
     *
     * @param string $offset The given offset.
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->getInputSource()->remove($offset);
    }

    /**
     * Check if an input element is set on the request.
     *
     * @param string $key The given key name.
     *
     * @return bool
     */
    public function __isset($key) : bool
    {
        return ! is_null($this->__get($key));
    }

    /**
     * Get an input element from the request.
     *
     * @param string The given key name.
     *
     * @return mixed
     */
    public function __get(string $key)
    {
        return Arr::get($this->all(), $key);
    }

    /**
     * Get the output with the request.
     *
     * @return string
     */
    public function __toString() : string
    {
        return sprintf('%s %s %s', $this->getMethod(), $this->getUri(), $this->getMessageBody());
    }
}
