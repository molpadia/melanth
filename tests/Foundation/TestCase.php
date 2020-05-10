<?php

namespace Melanth\Tests\Foundation;

use Traversable;
use SplFileInfo;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    public function assertIterator($expected, Traversable $iterator)
    {
        $actual = array_map(function (SplFileInfo $file) {
            return str_replace('/', DIRECTORY_SEPARATOR, $file->getPathname());
        }, iterator_to_array($iterator, false));

        sort($expected);
        sort($actual);

        $this->assertEquals($expected, $actual);
    }
}
