<?php

namespace Melanth\Tests\Foundation;

use Melanth\Foundation\Config;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    public function testSet()
    {
        $config = new Config;
        $this->assertNull($config->set('foo', 'bar'));
        $this->assertSame('bar', $config->get('foo'));

        $config = new Config;
        $this->assertNull($config->set('foo.bar', 'baz'));
        $this->assertSame('baz', $config->get('foo.bar'));

        $config = new Config;
        $this->assertNull($config->set(['foo' => 'bar']));
        $this->assertSame('bar', $config->get('foo'));
        $this->assertNull($config->get('baz'));

        $config = new Config;
        $this->assertNull($config->set(['foo.bar' => 'baz']));
        $this->assertSame('baz', $config->get('foo.bar'));

        $config = new Config;
        $config['foo'] = 'bar';
        $this->assertSame('bar', $config['foo']);

        $config = new Config;
        $config['foo.bar'] = 'baz';
        $this->assertSame('baz', $config['foo.bar']);
    }

    public function testAll()
    {
        $config = new Config(['foo' => 'bar']);
        $this->assertSame(['foo' => 'bar'], $config->all());
    }

    public function testHas()
    {
        $config = new Config;
        $config->set('foo.bar', 'baz');

        $this->assertTrue($config->has('foo'));
        $this->assertTrue($config->has('foo.bar'));
        $this->assertFalse($config->has('foo.baz'));

        $this->assertTrue(isset($config['foo']));
        $this->assertTrue(isset($config['foo.bar']));
        $this->assertFalse(isset($config['foo.baz']));
    }

    public function testUnset()
    {
        $config = new Config;
        $config->set(['foo.bar' => 'baz']);
        $this->assertSame('baz', $config->get('foo.bar'));

        unset($config['foo.bar']);

        $this->assertNull($config->get('foo.bar'));
    }
}
