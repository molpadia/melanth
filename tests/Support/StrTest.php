<?php

namespace Melanth\Support;

use Melanth\Support\Str;
use PHPUnit\Framework\TestCase;

class StrTest extends TestCase
{
    public function testCamel()
    {
        $this->assertSame('fooBar', Str::camel('FooBar'));
        $this->assertSame('fooBar', Str::camel('foo bar'));
        $this->assertSame('fooBar', Str::camel('foo-bar'));
        $this->assertSame('fooBar', Str::camel('foo_bar'));
        $this->assertSame('fooBarBaz', Str::camel('foo-barBaz'));

        $this->assertSame('melanthPHPFramework', Str::camel('Melanth_p_h_p_framework'));
        $this->assertSame('melanthPHPFramework', Str::camel('Melanth-p-h-p-framework'));
        $this->assertSame('melanthPHPFramework', Str::camel('Melanth p h p framework'));
        $this->assertSame('melanthPHPFramework', Str::camel('Melanth_p_h_p_framework'));
        $this->assertSame('melanthPHPFramework', Str::camel('Melanth_- -_p_- -_h_- -_p_- -_framework'));
    }

    public function testStudly()
    {
        $this->assertSame('FooBar', Str::studly('FooBar'));
        $this->assertSame('FooBar', Str::studly('foo bar'));
        $this->assertSame('FooBar', Str::studly('foo-bar'));
        $this->assertSame('FooBar', Str::studly('foo_bar'));
        $this->assertSame('FooBarBaz', Str::studly('foo-barBaz'));

        $this->assertSame('MelanthPHPFramework', Str::studly('melanth_p_h_p_framework'));
        $this->assertSame('MelanthPHPFramework', Str::studly('melanth-p-h-p-framework'));
        $this->assertSame('MelanthPHPFramework', Str::studly('melanth p h p framework'));
        $this->assertSame('MelanthPHPFramework', Str::studly('melanth_p_h_p_framework'));
        $this->assertSame('MelanthPHPFramework', Str::studly('melanth_- -_p_- -_h_- -_p_- -_framework'));
    }

    public function testSnake()
    {
        $this->assertSame('foo_bar_baz', Str::snake('fooBarBaz'));
        $this->assertSame('foo_bar_baz', Str::snake('fooBarBaz'));
        $this->assertSame('foo_bar_baz', Str::snake('Foo  Bar  Baz'));

        $this->assertSame('melanth_p_h_p_framework', Str::snake('MelanthPHPFramework'));
        $this->assertSame('melanth_php_framework', Str::snake('MelanthPhpFramework'));
        $this->assertSame('melanth php framework', Str::snake('MelanthPhpFramework', ' '));
        $this->assertSame('melanth__php__framework', Str::snake('MelanthPhpFramework', '__'));
        $this->assertSame('melanth_php_framework_', Str::snake('melanth_php_framework_'));
        $this->assertSame('melanth_php_framework', Str::snake('melanth php framework'));

        $this->assertSame('żółtałódka', Str::snake('ŻółtaŁódka'));
    }

    public function testStartsWith()
    {
        $this->assertTrue(Str::startsWith('foobarbaz', 'foo'));
        $this->assertTrue(Str::startsWith('foobarbaz', ['foo']));
        $this->assertTrue(Str::startsWith('foobarbaz', ['bar', 'foo']));
        $this->assertTrue(Str::startsWith('foobarbaz', 'foo'));
        $this->assertTrue(Str::startsWith('0123', 0));
        $this->assertTrue(Str::startsWith('żółtałódka', 'żół'));

        $this->assertFalse(Str::startsWith(0123, '0'));
        $this->assertFalse(Str::startsWith('0123', null));
        $this->assertFalse(Str::startsWith('foobarbaz', ''));
        $this->assertFalse(Str::startsWith('foobarbaz', []));
        $this->assertFalse(Str::startsWith('foobarbaz', 'bar'));
        $this->assertFalse(Str::startsWith('Foobarbaz', 'foo'));
        $this->assertFalse(Str::startsWith('foobarbaz', ['fooo', 'bar', 'baz']));
    }

    public function testEndsWith()
    {
        $this->assertTrue(Str::endsWith('foobarbaz', 'baz'));
        $this->assertTrue(Str::endsWith('foobarbaz', ['baz']));
        $this->assertTrue(Str::endsWith('foobarbaz', ['foo', 'baz']));
        $this->assertTrue(Str::endsWith('foobarbaz', 'baz'));
        $this->assertTrue(Str::endsWith('0123', 3));
        $this->assertTrue(Str::endsWith('żółtałódka', 'dka'));

        $this->assertFalse(Str::endsWith(0123, '31'));
        $this->assertFalse(Str::endsWith('0123', null));
        $this->assertFalse(Str::endsWith('foobarbaz', ''));
        $this->assertFalse(Str::endsWith('foobarbaz', []));
        $this->assertFalse(Str::endsWith('foobarbaz', 'Baz'));
        $this->assertFalse(Str::endsWith('FoobarBaz', 'baz'));
        $this->assertFalse(Str::endsWith('foobarbaz', ['bbaz', 'bar', 'foo']));
    }

    public function testContains()
    {
        $this->assertTrue(Str::contains('foobarbaz', 'foo'));
        $this->assertTrue(Str::contains('foobarbaz', 'bar'));
        $this->assertTrue(Str::contains('foobarbaz', 'baz'));
        $this->assertTrue(Str::contains('fooBarbaz', 'Bar'));
        $this->assertTrue(Str::contains('foobarbaz', ['baz']));
        $this->assertTrue(Str::contains('foobarbaz', ['food', 'baz']));
        $this->assertTrue(Str::contains('0123', 3));
        $this->assertTrue(Str::contains('żółtałódka', 'dka'));

        $this->assertFalse(Str::contains(0123, '31'));
        $this->assertFalse(Str::contains('0123', null));
        $this->assertFalse(Str::contains('foobarbaz', ''));
        $this->assertFalse(Str::contains('foobarbaz', []));
        $this->assertFalse(Str::contains('fooBarbaz', 'bazz'));
        $this->assertFalse(Str::contains('fooBarBaz', 'bar'));
        $this->assertFalse(Str::contains('foobarbaz', ['bbaz', 'dbar', 'food']));
    }

    public function testLimit()
    {
        $this->assertSame('abcdefghijklmnopqrstuvwxyz', Str::limit('abcdefghijklmnopqrstuvwxyz'));
        $this->assertSame('abcdefghij...', Str::limit('abcdefghijklmnopqrstuvwxyz', 10));
        $this->assertSame('abcdefghij', Str::limit('abcdefghijklmnopqrstuvwxyz', 10, ''));

        $nonAsciiCharacters = '一二三四五六七八九十';
        $this->assertSame('一二...', Str::limit($nonAsciiCharacters, 5));
        $this->assertSame('一二', Str::limit($nonAsciiCharacters, 5, ''));
    }

    public function testUpper()
    {
        $this->assertSame('FOO_BAR_BAZ', Str::upper('foo_bar_baz'));
        $this->assertSame('FOO BAR BAZ', Str::upper('Foo Bar Baz'));
    }

    public function testLower()
    {
        $this->assertSame('foo_bar_baz', Str::lower('FOO_BAR_BAZ'));
        $this->assertSame('foo bar baz', Str::lower('Foo bAr baZ'));
    }

    public function testSubstr()
    {
        $characters = 'foo_bar_baz';
        $this->assertSame('foo_bar_baz', Str::substr($characters));
        $this->assertSame('oo_bar_baz', Str::substr($characters, 1));
        $this->assertSame('oo_ba', Str::substr($characters, 1 ,5));
        $this->assertSame('foo_bar_baz', Str::substr($characters, 0 , strlen($characters)));
        $this->assertSame('ółtał', Str::substr('żółtałódka', 1, 5));
    }

    public function testLength()
    {
        $this->assertSame(11, Str::length('foo bar baz'));
        $this->assertSame(11, Str::length('foo bar baz', 'UTF-8'));
    }

    public function testUcFirst()
    {
        $this->assertSame('Foo_baz_bar', Str::ucfirst('foo_baz_bar'));
    }

    public function testParseCallback()
    {
        $this->assertSame(['foo', 'bar'], Str::parseCallback('foo@bar'));
        $this->assertSame(['foo', 'bar@baz'], Str::parseCallback('foo@bar@baz'));
        $this->assertSame(['foo', ''], Str::parseCallback('foo@'));
        $this->assertSame(['foo', null], Str::parseCallback('foo'));
        $this->assertSame(['foo', 'default'], Str::parseCallback('foo', 'default'));
    }
}
