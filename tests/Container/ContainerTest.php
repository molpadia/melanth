<?php

namespace Melanth\Tests\Container;

use stdClass;
use Closure;
use LogicException;
use ReflectionClass;
use ReflectionException;
use UnexpectedValueException;
use Melanth\Container\Container;
use Melanth\Contracts\Container\Container as ContainerContract;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testMakeConcreteBasicUsage()
    {
        $container = new Container;
        $this->assertFalse($container->bound(ContainerConcreteStub::class));
        $this->assertInstanceOf(ContainerConcreteStub::class, $container->make(ContainerConcreteStub::class));

        $container = new Container;
        $container->bind(ContainerConcreteStub::class);
        $this->assertTrue($container->bound(ContainerConcreteStub::class));
        $this->assertInstanceOf(ContainerConcreteStub::class, $container->make(ContainerConcreteStub::class));

        $container = new Container;
        $container->bind(ContainerConcreteStub::class, function () {
            return new ContainerConcreteStub;
        });
        $this->assertInstanceOf(ContainerConcreteStub::class, $container->make(ContainerConcreteStub::class));

        $container = new Container;
        $container->bind(ContainerConcreteContract::class, ContainerConcreteStub::class);
        $this->assertInstanceOf(ContainerConcreteStub::class, $container->make(ContainerConcreteContract::class));
        $this->assertInstanceOf(ContainerConcreteContract::class, $container->make(ContainerConcreteContract::class));
    }

    public function testBindAliasConcrete()
    {
        $container = new Container;
        $container->alias(ContainerConcreteStub::class, 'stub');
        $this->assertSame(ContainerConcreteStub::class, $container->getAlias('stub'));
        $this->assertInstanceOf(ContainerConcreteStub::class, $container->make('stub'));
    }

    public function testMakeNestedBindings()
    {
        $container = new Container;
        $concrete = $container->make(ContainerNestedDependentStub::class);
        $this->assertInstanceOf(ContainerNestedDependentStub::class, $concrete);
        $this->assertInstanceOf(ContainerDependentStub::class, $concrete->stub);
        $this->assertInstanceOf(ContainerConcreteStub::class, $concrete->stub->stub);
    }

    public function testMakeInstantiatedInstance()
    {
        $container = new Container;
        $container->instance('foo', 'bar');
        $container->instance('stub', $concrete = new ContainerConcreteStub);
        $this->assertTrue($container->bound('foo'));
        $this->assertTrue($container->bound('stub'));
        $this->assertSame('bar', $container->make('foo'));
        $this->assertSame($concrete, $container->make('stub'));
    }

    public function testMakeConcreteWithParameters()
    {
        $container = new Container;
        $container->bind(ContainerWithBinding::class, function ($container, $parameters) {
            $this->assertInstanceOf(Container::class, $container);

            return new ContainerWithBinding(...$parameters);
        });

        $binding = $container->makeWith(ContainerWithBinding::class, ['bar']);
        $this->assertInstanceOf(ContainerWithBinding::class, $binding);
        $this->assertSame('bar', $binding->stub);
    }

    public function testMakeConcreteWithDefaultValues()
    {
        $container = new Container;
        $concrete = $container->make(ContainerWithDefaultValues::class);
        $this->assertInstanceOf(ContainerWithDefaultValues::class, $concrete);
        $this->assertNull($concrete->stub);
        $this->assertSame('foo', $concrete->default);
    }

    public function testMakeWithOverridedParameters()
    {
        $binding = new ContainerDependentStub(new ContainerConcreteStub);
        $binding->stub = 'foo';
        $container = new Container;
        $container->instance(ContainerDependentStub::class, $binding);
        $concrete = $container->makeWith(ContainerNestedDependentStub::class, ['stub' => $binding]);
        $this->assertInstanceOf(ContainerDependentStub::class, $concrete->stub);
        $this->assertSame('foo', $concrete->stub->stub);
    }

    public function testMakeUnrecognizedConcrete()
    {
        $this->expectException(ReflectionException::class);
        $this->expectExceptionMessage('Class \\Melanth\\Test\\Container\\NonExistingStub does not exist');
        $container = new Container;
        $container->make('\\Melanth\\Test\\Container\\NonExistingStub');
    }

    public function testMakeUnqualifiedInstance()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Unrecognized class '.ContainerConcreteContract::class);
        $container = new Container;
        $container->make(ContainerConcreteContract::class);
    }

    public function testMakeUndefinedBinding()
    {
        $this->expectException(UnexpectedValueException::class);
        $this->expectExceptionMessage('Undefiend default value of parameter.');
        $container = new Container;
        $container->make(ContainerWithBinding::class);
    }

    public function testSetAliasWithNameConflict()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('[foo] cannot alias itself.');
        $container = new Container;
        $container->alias('foo', 'foo');
    }

    public function testExtendConcreteBasicUsage()
    {
        $container = new Container;
        $container->instance(ContainerWithBinding::class, new ContainerWithBinding('bar'));
        $this->assertNull($container->make(ContainerWithDefaultValues::class)->stub);

        unset($container[ContainerWithDefaultValues::class]);

        $container->extend(ContainerWithDefaultValues::class, function ($binding, $container) {
            $binding->stub = $container->make(ContainerWithBinding::class)->stub;

            return $binding;
        });
        $this->assertSame('bar', $container->make(ContainerWithDefaultValues::class)->stub);
    }

    public function testMakeContainerAsGlobal()
    {
        $container = Container::setInstance(Container::getInstance());
        $this->assertInstanceOf(ContainerContract::class, $container);
        $this->assertInstanceOf(Container::class, $container);
    }

    public function testWithArrayAccess()
    {
        $container = new Container;
        $container[ContainerConcreteStub::class] = new ContainerConcreteStub;
        $this->assertInstanceOf(ContainerConcreteStub::class, $container[ContainerConcreteStub::class]);
        $this->assertTrue(isset($container[ContainerConcreteStub::class]));

        unset($container[ContainerConcreteStub::class]);
        $this->assertFalse(isset($container[ContainerConcreteStub::class]));
    }
}

interface ContainerConcreteContract
{

}

class ContainerConcreteStub implements ContainerConcreteContract
{

}

class ContainerWithBinding
{
    public $stub;

    public function __construct($stub)
    {
        $this->stub = $stub;
    }
}

class ContainerDependentStub
{
    public $stub;

    public function __construct(ContainerConcreteStub $stub)
    {
        $this->stub = $stub;
    }
}

class ContainerWithDefaultValues
{
    public $stub;
    public $default;

    public function __construct($stub = null, $default = 'foo')
    {
        $this->stub = $stub;
        $this->default = $default;
    }
}

class ContainerNestedDependentStub
{
    public $stub;

    public function __construct(ContainerDependentStub $stub)
    {
        $this->stub = $stub;
    }
}
