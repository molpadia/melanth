<?php

namespace Melanth\Filesystem;

use AppendIterator;
use Countable;
use IteratorAggregate;
use InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Melanth\Filesystem\Iterators\FilenameIterator;

class Finder implements Countable, IteratorAggregate
{
    /**
     * The available filename patterns.
     *
     * @var array
     */
    protected $names = [];

    /**
     * The file directories.
     *
     * @var array
     */
    protected $directories = [];

    /**
     * Create a new finder instance.
     *
     * @return \Melanth\Filesystem\Finder
     */
    public static function create() : Finder
    {
        return new static;
    }

    /**
     * Set filename patterns.
     *
     * @param string|array $names The filename patterns.
     *
     * @return \Melanth\Filesystem\Finder
     */
    public function name($names) : Finder
    {
        $this->names = is_array($names) ? $names : func_get_args();

        return $this;
    }

    /**
     * Set file directories.
     *
     * @param string|array $directory The given directories.
     *
     * @return $this
     */
    public function in($directories) : Finder
    {
        $this->directories = is_array($directories) ? $directories : func_get_args();

        return $this;
    }

    /**
     * Get the total number of the files.
     *
     * @return int
     */
    public function count() : int
    {
        return iterator_count($this->getIterator());
    }

    /**
     * Get an iterator for the items.
     *
     * @return mixed
     */
    public function getIterator()
    {
        if (! $this->directories) {
            throw new InvalidArgumentException('The directories must be specified.');
        }

        if (count($this->directories) === 1) {
            return $this->createIterator($this->directories[0]);
        }

        return $this->createMultipleIterator();
    }

    /**
     * Create a new file iterator by given directory.
     *
     * @param string $directory The directory.
     *
     * @return \Iterator
     */
    protected function createIterator(string $directory)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );

        if ($this->names) {
            $iterator = new FilenameIterator($iterator, $this->names);
        }

        return $iterator;
    }

    /**
     * Create a multiple iterator by given directories.
     *
     * @return \AppendIterator
     */
    protected function createMultipleIterator() : AppendIterator
    {
        $iterator = new AppendIterator;

        foreach ($this->directories as $directory) {
            $iterator->append($this->createIterator($directory));
        }

        return $iterator;
    }
}
