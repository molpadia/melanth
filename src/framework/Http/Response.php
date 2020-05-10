<?php

namespace Melanth\Http;

use ArrayObject;
use JsonSerializable;
use Melanth\Contracts\Http\Response as ResponseContract;
use Melanth\Contracts\Support\Arrayable;
use Melanth\Contracts\Support\Jsonable;
use Melanth\Http\Exceptions\HttpResponseException;

class Response implements ResponseContract
{
    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_CONTINUE = 100;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_SWITCHING_PROTOCOLS = 101;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_PROCESSING = 102;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_EARLY_HINTS = 103;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_OK = 200;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_CREATED = 201;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_ACCEPTED = 202;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_NO_CONTENT = 204;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_RESET_CONTENT = 205;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_PARTIAL_CONTENT = 206;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_MULTI_STATUS = 207;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_ALREADY_REPORTED = 208;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_IM_USED = 226;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_MULTIPLE_CHOICES = 300;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_MOVED_PERMANENTLY = 301;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_FOUND = 302;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_SEE_OTHER = 303;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_NOT_MODIFIED = 304;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_USE_PROXY = 305;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_RESERVED = 306;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_TEMPORARY_REDIRECT = 307;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_PERMANENTLY_REDIRECT = 308;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_BAD_REQUEST = 400;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_UNAUTHORIZED = 401;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_PAYMENT_REQUIRED = 402;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_FORBIDDEN = 403;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_NOT_FOUND = 404;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_METHOD_NOT_ALLOWED = 405;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_NOT_ACCEPTABLE = 406;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_REQUEST_TIMEOUT = 408;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_CONFLICT = 409;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_GONE = 410;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_LENGTH_REQUIRED = 411;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_PRECONDITION_FAILED = 412;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_REQUEST_URI_TOO_LONG = 414;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_EXPECTATION_FAILED = 417;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_I_AM_A_TEAPOT = 418;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_MISDIRECTED_REQUEST = 421;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_UNPROCESSABLE_ENTITY = 422;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_LOCKED = 423;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_FAILED_DEPENDENCY = 424;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_INTERNAL_SERVER_ERROR = 500;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_NOT_IMPLEMENTED = 501;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_BAD_GATEWAY = 502;

    /**
     * The HTTP response status code.
     *
     * @var int
     */
    public const HTTP_SERVICE_UNAVAILABLE = 503;

    /**
     * The mapping of the response status representation.
     *
     * @var array
     */
    public const HTTP_STATUS_MAP = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',
        103 => 'Early Hints',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',
        208 => 'Already Reported',
        226 => 'IM Used',
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Payload Too Large',
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',
        421 => 'Misdirected Request',
        422 => 'Unprocessable Entity',
        423 => 'Locked',
        424 => 'Failed Dependency',
        425 => 'Too Early',
        426 => 'Upgrade Required',
        428 => 'Precondition Required',
        429 => 'Too Many Requests',
        431 => 'Request Header Fields Too Large',
        451 => 'Unavailable For Legal Reasons',
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',
        507 => 'Insufficient Storage',
        508 => 'Loop Detected',
        510 => 'Not Extended',
        511 => 'Network Authentication Required',
    ];

    /**
     * HTTP protocol version.
     *
     * @var string
     */
    protected $version = '1.0';

    /**
     * The response message body.
     *
     * @var string
     */
    protected $content;

    /**
     * HTTP status code.
     *
     * @var int
     */
    protected $statusCode;

    /**
     * HTTP header entities.
     *
     * @var \Melanth\Http\Headers
     */
    protected $headers;

    /**
     * The character encoding attribute.
     *
     * @var string
     */
    protected $charset;

    /**
     * Craete a new HTTP response instance.
     *
     * @param mixed    $content    The response content body.
     * @param int|null $statusCode The HTTP response status code.
     * @param array    $headers    The HTTP header entities.
     *
     * @return void
     */
    public function __construct($content = null, int $statusCode = null, array $headers = [])
    {
        $this->statusCode = $statusCode ?? self::HTTP_OK;
        $this->headers = new Headers($headers);
        $this->setContent($content);
    }

    /**
     * Create an instance directly.
     *
     * @param mixed    $content    The response content body.
     * @param int|null $statusCode The HTTP response status code.
     * @param array    $headers    The HTTP header entities.
     *
     * @return $this
     */
    public static function create($content = null, int $statusCode = null, array $headers = []) : Response
    {
        return new static($content, $statusCode, $headers);
    }

    /**
     * Prepare to handle an incoming HTTP request.
     *
     * @param \Melanth\Http\Request $requesst The request instance.
     *
     * @return $this
     */
    public function prepare(Request $request) : Response
    {
        if ($request->getServerConfig()->get('SERVER_PROTOCOL') !== 'HTTP/1.0') {
            $this->setVersion('1.1');
        }

        if ($request->isMethod('HEAD')) {
            $this->setContent(null);
        }

        $contentType = $this->headers->get('Content-Type');

        if ($this->isInformation() || $this->isEmpty()) {
            $this->headers->remove('Content-Type')->remove('Content-Length');
            $this->setContent(null);
        }
        // Add a suffix UTF-8 charset if text content does not contain any attributes.
        elseif (stripos($contentType, 'text/') === 0 && stripos($contentType, 'charset') === false) {
            $this->headers->set('Content-Type', $contentType.';charset='.$this->getCharset());
        }

        return $this;
    }

    /**
     * Send HTTP header entities.
     *
     * @todo HTTP cookie still in progress.
     *
     * @return $this
     */
    public function sendHeaders() : Response
    {
        if (headers_sent()) {
            return $this;
        }

        foreach ($this->headers->all() as $key => $value) {
            $replaced = strcasecmp($key, 'Content-Type') === 0;

            header($key.': '.$value, $replaced, $this->statusCode);
        }

        header($this->getMessageBody());

        return $this;
    }

    /**
     * Get the message body with the response.
     *
     * @return string
     */
    public function getMessageBody() : string
    {
        return sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->getStatusText());
    }

    /**
     * Send a message content with the response.
     *
     * @return $this
     */
    public function sendContent() : Response
    {
        echo $this->content;

        return $this;
    }

    /**
     * Send HTTP response message.
     *
     * @return $this
     */
    public function send() : Response
    {
        $this->sendHeaders()->sendContent();

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif (! in_array(PHP_SAPI, ['cli', 'phpdbg'], true)) {
            ob_end_flush();
        }

        return $this;
    }

    /**
     * Set the content to the response.
     * If the content is JSONable, we will set the appropriate header and convert to
     * the content to JSON. This is useful when returning something like models
     * from routes that will be automatically transformed to JSON format.
     *
     * @param mixed $content The input content data.
     *
     * @return $this
     */
    public function setContent($content) : Response
    {
        if ($this->shouldBeJson($content)) {
            $this->headers->set('Content-Type', 'application/json');

            $content = $this->morphToJson($content);
        }

        $this->content = (string) $content;

        return $this;
    }

    /**
     * Determine whether the given content should be turned into JSON.
     *
     * @param mixed $content The input content data.
     *
     * @return bool
     */
    protected function shouldBeJson($content) : bool
    {
        return $content instanceof Arrayable ||
               $content instanceof ArrayObject ||
               $content instanceof Jsonable ||
               $content instanceof JsonSerializable ||
               is_array($content);
    }

    /**
     * Mroph the given content into JSON.
     *
     * @param mixed $content The input content data.
     *
     * @return string
     */
    protected function morphToJson($content) : string
    {
        if ($content instanceof Jsonable) {
            return $content->toJson();
        } elseif ($content instanceof JsonSerializable) {
            $content = $content->jsonSerialize();
        } elseif ($content instanceof Arrayable) {
            $content = $content->toArray();
        }

        return json_encode($content);
    }

    /**
     * Add a header to the response.
     *
     * @param array $headers An array items of header.
     *
     * @return $this
     */
    public function withHeader(array $headers) : Response
    {
        foreach ($headers as $key => $value) {
            $this->headers->set($key, $value);
        }

        return $this;
    }

    /**
     * Throw an error exception instance.
     *
     * @throws \Melanth\Http\Exceptions\HttpResponseException
     */
    public function throwException() : HttpResponseException
    {
        throw new HttpResponseException($this);
    }

    /**
     * Determine whether HTTP status is an information.
     *
     * @return bool
     */
    public function isInformation() : bool
    {
        return $this->statusCode >= self::HTTP_CONTINUE && $this->statusCode < self::HTTP_OK;
    }

    /**
     * Determine whether HTTP status is empty.
     *
     * @return bool
     */
    public function isEmpty() : bool
    {
        return in_array($this->statusCode, [self::HTTP_NO_CONTENT, self::HTTP_NOT_MODIFIED]);
    }

    /**
     * Determine whether HTTP status is redirect.
     *
     * @return bool
     */
    public function isRedirect() : bool
    {
        return in_array($this->statusCode, [
            self::HTTP_CREATED,
            self::HTTP_MOVED_PERMANENTLY,
            self::HTTP_FOUND,
            self::HTTP_SEE_OTHER,
            self::HTTP_TEMPORARY_REDIRECT,
            self::HTTP_PERMANENTLY_REDIRECT
        ]);
    }

    /**
     * Set a HTTP charset attribute with the response.
     *
     * @param string $charset The charset
     *
     * @return $this
     */
    public function setCharset(string $charset) : Response
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Set a HTTP versoin with the response.
     *
     * @param string $version The HTTP version.
     *
     * @return string
     */
    public function setVersion(string $version) : Response
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get the HTTP charset with the response.
     *
     * @return string
     */
    public function getCharset() : string
    {
        return $this->charset ?: 'UTF-8';
    }

    /**
     * Get HTTP status code.
     *
     * @return int
     */
    public function status() : int
    {
        return $this->getStatusCode();
    }

    /**
     * Get the content of the response.
     *
     * @return mixed
     */
    public function content()
    {
        return $this->getContent();
    }

    /**
     * Set HTTP status code with the response.
     *
     * @param int $statusCode The HTTP status code.
     *
     * @return \Melanth\Http\Response
     */
    public function setStatusCode(int $stautsCode) : Response
    {
        $this->statusCode = $stautsCode;

        return $this;
    }

    /**
     * Get the status code with the response.
     *
     * @return int
     */
    public function getStatusCode() : int
    {
        return $this->statusCode;
    }

    /**
     * Get the HTTP status text in correspond with status code.
     *
     * @return string
     */
    public function getStatusText() : string
    {
        return static::HTTP_STATUS_MAP[$this->statusCode] ?? '';
    }

    /**
     * Get HTTP header entities.
     *
     * @return \Melanth\Http\Headers
     */
    public function headers() : Headers
    {
        return $this->headers;
    }

    /**
     * Get HTTP header entities.
     *
     * @return \Melanth\Http\Headers
     */
    public function getHeaders() : Headers
    {
        return $this->headers();
    }

    /**
     * Get the response content body.
     *
     * @return string
     */
    public function getContent() : string
    {
        return $this->content;
    }

    /**
     * Get the HTTP protocol version.
     *
     * @return string
     */
    public function getVersion() : string
    {
        return $this->version;
    }

    /**
     * Convert the response into string context.
     *
     * @return string
     */
    public function __toString() : string
    {
        return implode("\r\n", [$this->getMessageBody(), (string) $this->headers, $this->getContent()]);
    }
}
