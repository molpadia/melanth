<?php

namespace Mealnth\Contracts\Routing;

interface Router
{
    /**
     * Register a GET HTTP method request with the router.
     *
     * @param string $uri    The route URI.
     * @param mixed  $action The action with the router.
     *
     * @return \Melanth\Routing\Route
     */
    public function get(string $uri, $action);

    /**
     * Register a POST HTTP method request with the router.
     *
     * @param string $uri    The route URI.
     * @param mixed  $action The action with the router.
     *
     * @return \Melanth\Routing\Route
     */
    public function post(string $uri, $action);

    /**
     * Register a PUT HTTP method request with the router.
     *
     * @param string $uri    The route URI.
     * @param mixed  $action The action with the router.
     *
     * @return \Melanth\Routing\Route
     */
    public function put(string $uri, $action);

    /**
     * Register a PATCH HTTP method request with the router.
     *
     * @param string $uri    The route URI.
     * @param mixed  $action The action with the router.
     *
     * @return \Melanth\Routing\Route
     */
    public function patch(string $uri, $action);

    /**
     * Register a DELETE HTTP method request with the router.
     *
     * @param string $uri    The route URI.
     * @param mixed  $action The action with the router.
     *
     * @return \Melanth\Routing\Route
     */
    public function delete(string $uri, $action);

    /**
     * Register a HEAD HTTP method request with the router.
     *
     * @param string $uri    The route URI.
     * @param mixed  $action The action with the router.
     *
     * @return \Melanth\Routing\Route
     */
    public function head(string $uri, $action);

    /**
     * Register a OPTIONS HTTP method request with the router.
     *
     * @param string $uri    The route URI.
     * @param mixed  $action The action with the router.
     *
     * @return \Melanth\Routing\Route
     */
    public function options(string $uri, $action);

    /**
     * Register any HTTP method requests with the router.
     *
     * @param array|string $methods The HTTP methods.
     * @param string       $uri     The route URI.
     * @param mixed        $action  The action with the router.
     *
     * @return \Melanth\Routing\Route
     */
    public function any($methods, string $uri, $action);
}
