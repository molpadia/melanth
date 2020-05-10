<?php

namespace Melanth\Http\Exceptions;

use Exception;
use RuntimeException;

class HttpException extends RuntimeException
{
    /**
     * The HTTP status code.
     *
     * @var int
     */
    protected $statusCode;

    /**
     * The HTTP header entities.
     *
     * @var array
     */
    protected $headers = [];

    /**
     * Create a new HTTP exception instance.
     *
     * @param int        $statusCode The HTTP status code.
     * @param string     $message    The message body.
     * @param \Exception $previous   The previous exception instance.
     * @param array      $headers    The headers entities.
     * @param int        $code       The internal exception code.
     *
     * @return void
     */
    public function __construct(
        int $statusCode, string $message = null, Exception $previous = null, array $headers = [], int $code = 0
    ) {
        $this->statusCode = $statusCode;
        $this->headers = $headers;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get HTTP status code.
     *
     * @return string
     */
    public function getStatusCode() : int
    {
        return $this->statusCode;
    }

    /**
     * Get HTTP header entities.
     *
     * @return array
     */
    public function getHeaders() : array
    {
        return $this->headers;
    }
}
