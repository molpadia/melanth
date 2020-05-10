<?php

namespace Melanth\Tests\Support;

use Melanth\Container\Container;
use Melanth\Support\Pipeline;
use PHPUnit\Framework\TestCase;

class PipelineTest extends TestCase
{
    private $pipeline;

    public function setUp() : void
    {
        parent::setUp();

        $this->pipeline = new Pipeline(new Container);
    }

    public function tearDown() : void
    {
        $this->pipeline = null;

        parent::tearDown();
    }

    public function testRunCallablePipelines()
    {
        $pipe = function (DependentStub $stub) {
            $stub->foo = 'bar';
            return $stub;
        };

        $this->assertSame('bar', $this->pipeline->via(new DependentStub)->then($pipe)->foo);
    }

    public function testRunMultipleCallablePipelines()
    {
        $pipes = [function (DependentStub $stub, $next) {
            $stub->foo = 'bar';
            return $next($stub);
        }];

        $result = $this->pipeline->via(new DependentStub)->through($pipes)->then(function ($stub) {
            return $stub;
        });

        $this->assertSame('bar', $result->foo);
    }

    public function testRunClassPipelines()
    {
        $result = $this->pipeline->through([ConcreteBindingStub::class])->then(function ($stub) {
            return $stub;
        });

        $this->assertInstanceOf(DependentStub::class, $result->stub);
        $this->assertSame('bar', $result->stub->foo);
    }

    public function testRunClassPipelinesWithDefaultParameters()
    {
        $result = $this->pipeline->via(new DependentStub)
            ->through(['\\Melanth\\Tests\\Support\\ConcreteBindingWithDefaultParameterStub:bar'])
            ->then(function ($stub) {
                return $stub;
            });

        $this->assertSame('bar', $result->foo);
    }

    public function testRunObjectPipelines()
    {
        $stub = new ConcreteBindingStub(new DependentStub);
        $result = $this->pipeline->through([$stub])->then(function ($stub) {
            return $stub;
        });

        $this->assertInstanceOf(DependentStub::class, $result->stub);
        $this->assertSame('bar', $result->stub->foo);
    }

    public function testRunObjectPipelinesWithInvokableMehtod()
    {
        $stub = new ConcreteBindingWithInvokableStub(new DependentStub);
        $result = $this->pipeline->via(new DependentStub)->through([$stub])->then(function ($stub) {
            return $stub;
        });

        $this->assertSame('bar', $result->foo);
    }

    public function testRunObjectPipelinesWithAlternativeMethod()
    {
        $stub = new ConcreteBindingWithAlternativeStub(new DependentStub);
        $result = $this->pipeline->setMethod('resolve')->via(new DependentStub)->through([$stub])->then(function ($stub) {
            return $stub;
        });

        $this->assertSame('bar', $result->foo);
    }
}

class DependentStub
{
    public $foo;
}

class ConcreteBindingStub
{
    public $stub;

    public function __construct(DependentStub $stub)
    {
        $this->stub = $stub;
    }

    public function handle($passable, $next)
    {
        $this->stub->foo = 'bar';
        return $next($this);
    }
}

class ConcreteBindingWithDefaultParameterStub
{
    public function handle($passable, $next, $parameters)
    {
        $passable->foo = $parameters;
        return $next($passable);
    }
}

class ConcreteBindingWithInvokableStub
{
    public function __invoke($passable, $next)
    {
        $passable->foo = 'bar';
        return $next($passable);
    }
}

class ConcreteBindingWithAlternativeStub
{
    public function resolve($passable, $next)
    {
        $passable->foo = 'bar';
        return $next($passable);
    }
}
