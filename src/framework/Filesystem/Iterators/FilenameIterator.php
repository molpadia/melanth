<?php

namespace Melanth\Filesystem\Iterators;

use FilterIterator;
use Iterator;

class FilenameIterator extends FilterIterator
{
    /**
     * The available filename patterns.
     *
     * @var string
     */
    protected $patterns = [];

    /**
     * Create a new filename filter iterator.
     *
     * @param \Iterator $iterator The file iterator.
     * @param array     $patterns The filename patterns.
     *
     * @return void
     */
    public function  __construct(Iterator $iterator, array $patterns)
    {
        parent::__construct($iterator);

        $this->patterns = $patterns;
    }

    /**
     * Determine whether the current file is acceptable.
     *
     * @return bool
     */
    public function accept() : bool
    {
        return $this->isAccepted($this->current()->getFilename());
    }

    /**
     * Determine whether the filename matches a pattern.
     *
     * @param string $filename The given filename.
     *
     * @return bool
     */
    protected function isAccepted(string $filename) : bool
    {
        foreach ($this->patterns as $pattern) {
            if (preg_match($pattern, $filename)) {
                return true;
            }
        }

        return false;
    }
}
