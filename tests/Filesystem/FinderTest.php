<?php

namespace Melanth\Tests\Filesystem;

use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use UnexpectedValueException;
use Melanth\Filesystem\Finder;
use Melanth\Tests\Foundation\TestCase;

class FinderTest extends TestCase
{
    public function testGetFilesBasicUsage()
    {
        $finder = new Finder;
        $destination = __DIR__.'/fixtures';
        $expected = [
            "{$destination}/foo.txt",
            "{$destination}/foo/bar.tmp",
            "{$destination}/foo/bar/baz.txt",
            "{$destination}/bar/baz.txt",
        ];

        $this->assertIterator($expected, $finder->in($destination));

        $expected = [
            "{$destination}/bar/baz.txt",
            "{$destination}/foo/bar/baz.txt",
        ];

        $this->assertIterator($expected, $finder->in([__DIR__.'/fixtures/bar', __DIR__.'/fixtures/foo/bar']));
        $this->assertIterator($expected, $finder->in(__DIR__.'/fixtures/bar', __DIR__.'/fixtures/foo/bar'));
    }

    public function testGetFilesByFilename()
    {
        $finder = new Finder;
        $destination = __DIR__.'/fixtures';
        $expected = [
            "{$destination}/foo.txt",
            "{$destination}/foo/bar/baz.txt",
            "{$destination}/bar/baz.txt",
        ];

        $this->assertIterator($expected, $finder->name('/\.txt/')->in($destination));
    }

    public function testGetFileWithoutDirectory()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The directories must be specified.');

        iterator_to_array(new Finder);
    }

    public function testCountFilesInDirectory()
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(__DIR__.'/fixtures', RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $this->assertSame(count(iterator_to_array($iterator)), count((new Finder)->in(__DIR__.'/fixtures')));
    }

    public function testGetFilesWithInvalidDirectory()
    {
        $this->expectException(UnexpectedValueException::class);

        iterator_to_array(Finder::create()->in('baz'));
    }
}
