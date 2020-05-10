<?php

namespace Melanth\Support;

use Closure;
use Melanth\Container\Container;
use Melanth\Support\Str;

class Pipeline
{
    /**
     * The object passed through the pipeline.
     *
     * @var object
     */
    protected $passable;

    /**
     * The array of pipe handles.
     *
     * @var array
     */
    protected $pipes = [];

    /**
     * The method invokation of the object.
     *
     * @var string
     */
    protected $method = 'handle';

    /**
     * Create a new pipeline instance.
     *
     * @param \Melanth\Container\Container $container The container instance.
     *
     * @return void
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Set an object passed through pipeline.
     *
     * @param object $passable The object passed through the pipeline.
     *
     * @return $this
     */
    public function via(object $passable) : Pipeline
    {
        $this->passable = $passable;

        return $this;
    }

    /**
     * Set the list of pipes.
     *
     * @param array $pipes The list of pipe handles.
     *
     * @return $this
     */
    public function through(array $pipes) : Pipeline
    {
        $this->pipes = $pipes;

        return $this;
    }

    /**
     * Set a method call on the pipes.
     *
     * @param string $method The method call on the pipes.
     *
     * @return $this
     */
    public function setMethod(string $method) : Pipeline
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Run the pipeline to the destination.
     *
     * @param \Closure $pipe The last pipe
     *
     * @return mixed
     */
    public function then(Closure $pipe)
    {
        $destination = function ($passable) use ($pipe) {
            return $pipe($passable);
        };

        $pipeline = array_reduce(
            array_reverse($this->pipes), $this->prepare(), $destination
        );

        return $pipeline($this->passable);
    }

    /**
     * Prepare the pipeline handles.
     *
     * @return \Closure
     */
    protected function prepare() : Closure
    {
        return function ($next, $pipe) {
            return function ($passable) use ($next, $pipe) {
                // If the given pipe is oin f   een instance of closure, call it directly.
                if (is_callable($pipe)) {
                    return $pipe($passable, $next);
                }
                // If the given pipe is a string, parse the result and resolve the class
                // out of dependency injection container.
                elseif (! is_object($pipe)) {
                    [$class, $parameters] = array_pad(explode(':', $pipe, 2), 2, []);

                    if (is_string($parameters)) {
                        $parameters = explode(',', $parameters);
                    }

                    $pipe = $this->container->make($class);

                    $parameters = array_merge([$passable, $next], $parameters);
                } else {
                    $parameters = [$passable, $next];
                }

                return method_exists($pipe, $this->method)
                    ? $pipe->{$this->method}(...$parameters)
                    : $pipe(...$parameters);
            };
        };
    }
}
