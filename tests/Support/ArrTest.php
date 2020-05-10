<?php

namespace Melanth\Tests\Support;

use stdClass;
use ArrayObject;
use Melanth\Support\Arr;
use PHPUnit\Framework\TestCase;

class ArrTest extends TestCase
{
    public function testAccessible()
    {
        $this->assertTrue(Arr::accessible([]));
        $this->assertTrue(Arr::accessible([1, 2]));
        $this->assertTrue(Arr::accessible(['a' => 1, 'b' => 2]));
        $this->assertTrue(Arr::accessible(new ArrayObject));

        $this->assertFalse(Arr::accessible(null));
        $this->assertFalse(Arr::accessible('abc'));
        $this->assertFalse(Arr::accessible(new stdClass));
        $this->assertFalse(Arr::accessible((object) ['a' => 1, 'b' => 2]));
    }

    public function testExcept()
    {
        $values = ['foo' => 'bar', 'boo' => 'bar'];
        $this->assertEquals(['foo' => 'bar'], Arr::except($values, 'boo'));
        $this->assertEquals(['foo' => 'bar'], Arr::except($values, ['boo']));
        $this->assertEquals(['foo' => []], Arr::except(['foo' => ['boo' => 'bar']], 'foo.boo'));
    }

    public function testExists()
    {
        $array = new ArrayObject(['foo' => 'bar']);
        $this->assertTrue(Arr::exists(['foo' => null], 'foo'));
        $this->assertTrue(Arr::exists(['foo' => 'bar'], 'foo'));
        $this->assertTrue(Arr::exists($array, 'foo'));
    }

    public function testSet()
    {
        $array = [];
        Arr::set($array, 'foo.boo', 'bar');
        $this->assertEquals(['foo' => ['boo' => 'bar']], $array);
        $this->assertEquals('foo', Arr::set($array, null, 'foo'));
    }

    public function testGet()
    {
        $this->assertNull(Arr::get('foo', 'boo'));

        $array = ['foo.boo' => 'bar'];
        $this->assertNull(Arr::get($array, 'foo'));
        $this->assertSame('bar', Arr::get($array, 'foo.boo'));

        $array = ['foo', 'bar'];
        $this->assertSame($array, Arr::get($array, null));

        $array = ['foo' => 'bar'];
        $this->assertSame('bar', Arr::get($array, 'foo'));

        $array = ['foo' => null, 'bar' => ['baz' => null]];
        $this->assertNull(Arr::get($array, 'foo'), 'default');
        $this->assertNull(Arr::get($array, 'bar.baz'), 'default');

        $array = new ArrayObject(['foo' => ['bar' => 'baz']]);
        $this->assertSame($array, Arr::get($array, null));
        $this->assertSame('baz', Arr::get($array, 'foo.bar'));
        $this->assertSame('default', Arr::get($array, 'baz', 'default'));
        $this->assertNull(Arr::get($array, 'foo.bar.baz'));

        $array = new ArrayObject(['foo' => ['boo' => 'bar']]);
        $this->assertSame('bar', Arr::get($array, 'foo.boo'));

        $arrayChild = new ArrayObject(['foo' => 'bar']);
        $arrayParent = ['parent' => new ArrayObject(['child' => $arrayChild])];
        $this->assertSame('bar', Arr::get($arrayParent, 'parent.child.foo'));

        $array = [
            'foo' => [
                ['name' => 'boo'],
                ['name' => 'baz'],
            ]
        ];

        $this->assertSame('boo', Arr::get($array, 'foo.0.name'));
        $this->assertSame('baz', Arr::get($array, 'foo.1.name'));
        $this->assertSame('default', Arr::get($array, 'foo.boo', 'default'));
        $this->assertSame('bar', Arr::get($array, 'foo.baz', function () {
            return 'bar';
        }));
    }

    public function testHas()
    {
        $this->assertFalse(Arr::has(null, 'foo'));
        $this->assertFalse(Arr::has([], 'foo'));
        $this->assertFalse(Arr::has(['foo'], []));

        $array = ['foo' => ['baz' => 'bar']];
        $this->assertTrue(Arr::has($array, 'foo'));
        $this->assertTrue(Arr::has($array, 'foo.baz'));
        $this->assertFalse(Arr::has($array, 'foo.baz.bar'));

        $array = ['foo' => ['baz' => null], 'baz' => ['bar' => null]];
        $this->assertTrue(Arr::has($array, ['foo.baz']));
        $this->assertTrue(Arr::has($array, ['foo', 'baz']));
        $this->assertTrue(Arr::has($array, ['foo.baz', 'baz.bar']));
        $this->assertFalse(Arr::has($array, ['foo.baz', 'foo.baz.bar']));

        $this->assertTrue(Arr::has(['' => 'foo'], ''));
        $this->assertTrue(Arr::has(['' => 'foo'], ['']));
        $this->assertFalse(Arr::has([''], ''));
        $this->assertFalse(Arr::has([], ''));
        $this->assertFalse(Arr::has([], ['']));
    }

    public function testRemove()
    {
        $array = ['foo' => ['baz' => 'bar']];
        Arr::remove($array, null);
        $this->assertSame(['foo' => ['baz' => 'bar']], $array);

        $array = ['foo' => ['baz' => 'bar']];
        Arr::remove($array, []);
        $this->assertSame(['foo' => ['baz' => 'bar']], $array);

        $array = ['foo' => ['baz' => 'bar']];
        Arr::remove($array, 'foo');
        $this->assertSame([], $array);

        $array = ['foo' => ['baz' => 'bar']];
        Arr::remove($array, 'foo.baz');
        $this->assertSame(['foo' => []], $array);

        $array = ['foo' => ['baz' => 'bar']];
        Arr::remove($array, ['bar.foo', 'foo.baz.bar']);
        $this->assertSame(['foo' => ['baz' => 'bar']], $array);

        $array = ['foo' => null, 'baz' => null, 'bar' => null];
        Arr::remove($array, ['foo', 'bar']);
        $this->assertSame(['baz' => null], $array);
    }

    public function testFirst()
    {
        $callback = function ($value, $key) {
            return $key === 'bar';
        };
        $this->assertNull(Arr::first([]));
        $this->assertNull(Arr::first(['foo' => 100], $callback));
        $this->assertSame('bar', Arr::first(['bar', 'foo']));
        $this->assertSame('bar', Arr::first(['foo' => 'bar']));
        $this->assertSame('default', Arr::first([], null, 'default'));
        $this->assertSame(1000, Arr::first(['foo' => 100, 'bar' => 1000], $callback));
    }

    public function testWhere()
    {
        $values = Arr::where([100, '200', 300, '400', 500], function ($value) {
            return is_string($value);
        });

        $this->assertSame([1 => '200', 3 => '400'], $values);
    }
}
