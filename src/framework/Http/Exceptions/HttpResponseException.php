<?php

namespace Melanth\Http\Exceptions;

use RuntimeException;
use Melanth\Http\Response;

class HttpResponseException extends RuntimeException
{
    /**
     * The underlying response instance.
     *
     * @var \Melanth\Http\Response
     */
    protected $response;

    /**
     * Create a new HTTP response exception.
     *
     * @param \Melanth\Http\Response $response The response instance.
     *
     * @return void
     */
    public function  __construct(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Get the underlying response instance.
     *
     * @return \Melanth\Http\Response
     */
    public function getResponse() : Response
    {
        return $this->response;
    }
}
