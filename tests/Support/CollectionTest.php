<?php

namespace Melanth\Tests;

use ArrayIterator;
use ArrayObject;
use JsonSerializable;
use Melanth\Contracts\Support\Arrayable;
use Melanth\Contracts\Support\Jsonable;
use Melanth\Support\Collection;
use PHPUnit\Framework\TestCase;

class CollectionTest extends TestCase
{
    public function testAll()
    {
        $this->assertSame(['foo' => 'bar'], Collection::make(['foo' => 'bar'])->all());
        $this->assertSame(['foo' => 'bar'], Collection::make(new Collection(['foo' => 'bar']))->all());
        $this->assertSame(['foo' => 'bar'], Collection::make(new AssociativeArrayableStub)->all());
        $this->assertSame(['foo' => 'bar'], Collection::make(new AssociativeJsonableStub)->all());
        $this->assertSame(['foo' => 'bar'], Collection::make(new JsonSerializableStub)->all());
        $this->assertSame(['foo' => 'bar'], Collection::make(new ArrayObject(['foo' => 'bar']))->all());
        $this->assertSame(['foo'], Collection::make('foo')->all());
    }

    public function testDiff()
    {
        $this->assertSame(['foo', 'bar'], Collection::make(['foo', 'bar', 'baz'])->diff('baz')->all());
        $this->assertSame(['foo', 'bar'], Collection::make(['foo', 'bar', 'baz'])->diff(new NumericArrayableStub)->all());
        $this->assertSame(['foo', 'bar'], Collection::make(['foo', 'bar', 'baz'])->diff(new NumericJsonableStub)->all());
        $this->assertSame(['foo', 'bar'], Collection::make(['foo', 'bar', 'baz'])->diff(new ArrayObject(['baz']))->all());
        $this->assertSame(['foo', 'bar'], Collection::make(['foo', 'bar', 'baz'])->diff(['baz'])->all());
    }

    public function testDiffKeys()
    {
        $items = ['baz' => null];
        $array = ['foo' => 'bar', 'baz' => 'bar'];
        $this->assertSame(['foo' => 'bar'], Collection::make($array)->diffKeys($items)->all());
        $this->assertSame(['foo' => 'bar'], Collection::make(new ArrayObject($array))->diffKeys($items)->all());
        $this->assertSame(['foo' => 'bar'], Collection::make(new AssociativeArrayableStub)->diffKeys($items)->all());
        $this->assertSame(['foo' => 'bar'], Collection::make(new AssociativeJsonableStub)->diffKeys($items)->all());
    }

    public function testCount()
    {
        $this->assertSame(1, Collection::make(['foo'])->count());
        $this->assertSame(1, Collection::make(new ArrayObject(['foo' => 'bar']))->count());
        $this->assertSame(1, Collection::make(new AssociativeArrayableStub)->count());
        $this->assertSame(1, Collection::make(new AssociativeJsonableStub)->count());
        $this->assertSame(1, Collection::make(new JsonSerializableStub)->count());
    }

    public function testEach()
    {
        $items = new Collection([100, 200, 300, 400, 500]);

        $items->each(function ($value) use (&$results) {
            if ($value === 300) {
                return false;
            }

            $results[] = $value;
        });

        $this->assertSame([100, 200], $results);
    }

    public function testSet()
    {
        $items = new Collection;
        $items['foo'] = 'bar';
        $this->assertSame(['foo' => 'bar'], $items->all());
        $this->assertSame(['foo'], Collection::make()->set(null, 'foo')->all());
        $this->assertSame(['foo' => 'bar'], Collection::make()->set('foo', 'bar')->all());
    }

    public function testGet()
    {
        $items = new Collection;
        $items['foo'] = 'bar';
        $this->assertSame('bar', $items['foo']);
        $this->assertNull((new Collection(['foo' => 'bar']))->get('bar'));
        $this->assertSame('baz', (new Collection(['foo' => 'bar']))->get('bar', 'baz'));
        $this->assertSame('bar', (new Collection(['foo' => 'bar']))->get('foo'));
    }

    public function testHas()
    {
        $this->assertTrue(Collection::make(['foo' => 'bar'])->has('foo'));
    }

    public function testRemove()
    {
        $this->assertSame(['foo' => null], Collection::make(['foo' => null, 'baz' => null])->remove('baz')->all());
        $this->assertSame(['foo' => null], Collection::make(['foo' => null, 'bar' => null, 'baz' => null])->remove(['bar', 'baz'])->all());
    }

    public function testAdd()
    {
        $this->assertSame(['foo'], Collection::make()->add('foo')->all());
        $this->assertSame([['foo']], Collection::make()->add(['foo'])->all());
    }

    public function testIterable()
    {
        $items = new Collection(['foo']);
        $this->assertInstanceOf(ArrayIterator::class, $items->getIterator());
        $this->assertEquals(['foo'], $items->getIterator()->getArrayCopy());
    }

    public function testOffsetExists()
    {
        $items = Collection::make(['foo' => 'bar']);
        $this->assertTrue(isset($items['foo']));
        $this->assertFalse(isset($items['bar']));

        $items = Collection::make(new ArrayObject(['foo' => 'bar']));
        $this->assertTrue(isset($items['foo']));
        $this->assertFalse(isset($items['bar']));

        $items = Collection::make(new AssociativeArrayableStub);
        $this->assertTrue(isset($items['foo']));
        $this->assertFalse(isset($items['bar']));

        $items = Collection::make(new AssociativeJsonableStub);
        $this->assertTrue(isset($items['foo']));
        $this->assertFalse(isset($items['bar']));

        $items = Collection::make(new JsonSerializableStub);
        $this->assertTrue(isset($items['foo']));
        $this->assertFalse(isset($items['bar']));
    }

    public function testOffsetUnset()
    {
        $items = Collection::make(['foo' => 'bar']);
        unset($items['foo']);
        $this->assertEmpty($items->all());
    }

    public function testToArray()
    {
        $this->assertSame(['foo', 'bar'], Collection::make(['foo', 'bar'])->toArray());
        $this->assertSame(['foo' => ['baz']], Collection::make(['foo' => new NumericArrayableStub])->toArray());
        $this->assertSame(['foo' => ['foo' => 'bar']], Collection::make(['foo' => new AssociativeArrayableStub])->toArray());
    }

    public function testFilter()
    {
        $callback = function ($value, $key) {
            return $key === 'foo';
        };

        $this->assertSame(['foo' => 'bar'], Collection::make(['foo' => 'bar', 'bar' => 'baz'])->filter($callback)->all());
        $this->assertSame(['foo' => 'bar'], Collection::make(['foo' => 'bar', 'bar' => null, 'baz' => []])->filter()->all());
    }

    public function testValues()
    {
        $this->assertInstanceOf(Collection::class, Collection::make(['foo' => 'bar'])->values());
        $this->assertSame(['bar'], Collection::make(['foo' => 'bar'])->values()->all());
    }

    public function testSerializable()
    {
        $this->assertSame(['foo' => 'bar'], Collection::make(['foo' => 'bar'])->jsonSerialize());
        $this->assertSame(['foo' => ['foo' => 'bar']], Collection::make(['foo' => new JsonSerializableStub])->jsonSerialize());
        $this->assertSame(['foo' => ['foo' => 'bar']], Collection::make(['foo' => new AssociativeArrayableStub])->jsonSerialize());
        $this->assertSame(['foo' => ['foo' => 'bar']], Collection::make(['foo' => new AssociativeJsonableStub])->jsonSerialize());
    }

    public function testToJson()
    {
        $this->assertSame('{"foo":"bar"}', Collection::make(['foo' => 'bar'])->toJson());
        $this->assertSame('{"foo":"bar"}', Collection::make(new AssociativeArrayableStub)->toJson());
        $this->assertSame('{"foo":"bar"}', Collection::make(new AssociativeJsonableStub)->toJson());
        $this->assertSame('{"foo":"bar"}', Collection::make(new JsonSerializableStub)->toJson());
        $this->assertSame('{"foo":"bar"}', Collection::make(new ArrayObject(['foo' => 'bar']))->toJson());
    }

    public function testConvertToString()
    {
        $this->assertSame('{"foo":"bar"}', (string) Collection::make(['foo' => 'bar']));
        $this->assertSame('{"foo":"bar"}', (string) Collection::make(new AssociativeArrayableStub));
        $this->assertSame('{"foo":"bar"}', (string) Collection::make(new AssociativeJsonableStub));
        $this->assertSame('{"foo":"bar"}', (string) Collection::make(new JsonSerializableStub)->toJson());
        $this->assertSame('{"foo":"bar"}', (string) Collection::make(new ArrayObject(['foo' => 'bar'])));
    }
}

class NumericArrayableStub implements Arrayable
{
    public function toArray() : array
    {
        return ['baz'];
    }
}

class NumericJsonableStub implements Jsonable
{
    public function toJson($options = 0) : string
    {
        return '["baz"]';
    }
}

class AssociativeArrayableStub implements Arrayable
{
    public function toArray() : array
    {
        return ['foo' => 'bar'];
    }
}

class AssociativeJsonableStub implements Jsonable
{
    public function toJson($options = 0) : string
    {
        return '{"foo": "bar"}';
    }
}

class JsonSerializableStub implements JsonSerializable
{
    public function jsonSerialize()
    {
        return ['foo' => 'bar'];
    }
}
