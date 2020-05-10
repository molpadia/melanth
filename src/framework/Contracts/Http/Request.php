<?php

namespace Melanth\Contracts\Http;

interface Request
{
    /**
     * Determine whetehr the given method is identical.
     *
     * @param string $method The HTTP method.
     *
     * @return bool
     */
    public function isMethod(string $method) : bool;

    /**
     * Get the HTTP request method.
     *
     * @return string
     */
    public function method() : string;

    /**
     * Get the HTTP scheme from the request.
     *
     * @return string
     */
    public function getScheme() : string;

    /**
     * Get the IP address from the server configuration.
     *
     * @return string
     */
    public function ip() : string;

    /**
     * Get the hostname with the request.
     *
     * @return string
     */
    public function getHost() : string;

    /**
     * Get the port number with the request.
     *
     * @return int
     */
    public function getPort() : int;

    /**
     * Get the url without query string from the request.
     *
     * @return string
     */
    public function url() : string;

    /**
     * Get HTTP URI with the request.
     *
     * @return string
     */
    public function getUri() : ?string;

    /**
     * Get the current path info with the request.
     *
     * @return string
     */
    public function path() : string;
}
