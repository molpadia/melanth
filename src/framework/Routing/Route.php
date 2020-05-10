<?php

namespace Melanth\Routing;

use LogicException;
use Melanth\Http\Request;
use Melanth\Routing\Matching\MethodValidator;
use Melanth\Routing\Matching\UriValidator;

class Route
{
    /**
     * The route action.
     *
     * @var array
     */
    protected $action;

    /**
     * The URI pattenr with the route.
     *
     * @var string
     */
    protected $uri;

    /**
     * The route parameters for method bindings.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * The parameter naems for the route.
     *
     * @var array
     */
    protected $parameterNames;

    /**
     * Create a new route instance.
     *
     * @param array|string   $methods The HTTP methods.
     * @param string         $uri     The route URI.
     * @param array|\Closure $action  The action controller.
     *
     * @return void
     */
    public function __construct($methods, $uri, $action)
    {
        $this->methods = (array) $methods;
        $this->uri = $uri;
        $this->action = RouteAction::parse($action);
    }

    /**
     * Bind the URI parametres with route names.
     *
     * @param \Melanth\Http\Request $request The request instance.
     *
     * @return $this
     */
    public function bind(Request $request) : Route
    {
        $this->parameters += (new RouteParameterBinder($this))->bind($request);

        return $this;
    }

    /**
     * Set a parameter to the route.
     *
     * @param string $name  The parameter name.
     * @param mixed  $value The parameter payload.
     *
     * @return $this
     */
    public function setParameter(string $name, $value) : Route
    {
        $this->parameters[$name] = $value;

        return $this;
    }

    /**
     * Get parameters with the route.
     *
     * @return array
     */
    public function parameters() : array
    {
        return $this->parameters;
    }

    /**
     * Validate an incoming request matches the route.
     *
     * @param \Melanth\Http\Request $request The incoming request instance.
     *
     * @return bool
     */
    public function matches(Request $request) : bool
    {
        foreach ($this->getValidators() as $validator) {
            if (! $validator->validate($this, $request)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the route validators.
     *
     * @return array
     */
    protected  function getValidators() : array
    {
        return [new UriValidator, new MethodValidator];
    }

    /**
     * Get the regex pattern of HTTP URI.
     *
     * @param bool $shouldGroup Determine whether match named group pattern.
     *
     * @return string
     */
    public function getRegex(bool $shouldGroup = false) : string
    {
        $replacement = $shouldGroup ? '(?<$1>[^/]+)' : '[^/]+';

        return sprintf('#^%s$#u', preg_replace('/\{(\w+)\}/', $replacement, $this->uri));
    }

    /**
     * Get the parameter names.
     *
     * @return array
     */
    public function parameterNames() : array
    {
        return $this->parameterNames ?? $this->parameterNames = $this->compileParameters();
    }

    /**
     * Get the parameter names for the route.
     *
     * @return array
     */
    protected function compileParameters() : array
    {
        preg_match_all('/\{(\w+)\}/', $this->uri, $matches);

        return $matches[1];
    }

    /**
     * Get the parameters without NULL.
     *
     * @return array
     */
    public function parametersWithoutEmpty() : array
    {
        return array_filter($this->parameters());
    }

    /**
     * Set the domain with the route.
     *
     * @param string $domain The doamin name.
     *
     * @return $this
     */
    public function setDomain(string $domain) : Route
    {
        $this->action['domain'] = $domain;

        return $this;
    }

    /**
     * Get the domain name.
     *
     * @return string|null
     */
    public function domain() : ?string
    {
        return $this->action['domain'] ?? null;
    }

    /**
     * Set the URI route path.
     *
     * @param string $uri The route URI.
     *
     * @return $this
     */
    public function setUri(string $uri) : Route
    {
        $this->uri = $uri === '' ? '/' : $uri;

        return $this;
    }

    /**
     * Get the URI route path.
     *
     * @return string
     */
    public function uri() : string
    {
        return $this->uri;
    }

    /**
     * Get HTTP methods with the route.
     *
     * @return array
     */
    public function methods() : array
    {
        return $this->methods;
    }

    /**
     * Set the route action with the route.
     *
     * @param array $action The route action.
     *
     * @return $this
     */
    public function setAction(array $action) : Route
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get route action with the route.
     *
     * @return array
     */
    public function getAction() : array
    {
        return $this->action;
    }
}
