<?php

namespace Melanth\Http;

use Melanth\Support\Collection;

class Headers extends Collection
{
    /**
     * Create a new HTTP headers collection.
     *
     * @param array $headers The array headers.
     *
     * @return void
     */
    public function __construct(array $headers = [])
    {
        parent::__construct($this->parseItems($headers));
    }

    /**
     * Normalize an array items.
     *
     * @param array $items The array headers.
     *
     * @return array
     */
    protected function parseItems(array $items) : array
    {
        $results = [];

        foreach ($items as $key => $value) {
            $results[$this->normalize($key)] = $value;
        }

        return $results;
    }

    /**
     * Set an item to the collection.
     *
     * @param string $key   The key name.
     * @param string $value The value.
     *
     * @return $this
     */
    public function set($key, $value)
    {
        return parent::set($this->normalize($key), $value);
    }

    /**
     * Determine whether the given header key exists in the collection.
     *
     * @param string $key The given key name.
     *
     * @return bool
     */
    public function has($key) : bool
    {
        return parent::has($this->normalize($key));
    }

    /**
     * Remove a header item from the collection by given key name.
     *
     * @param string $key The given key name.
     *
     * @return $this
     */
    public function remove($key) : self
    {
        return parent::remove($this->normalize($key));
    }

    /**
     * Get the HTTP header item from the collection.
     *
     * @param string      $key     The given key name.
     * @param string|null $default The default value.
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        return parent::get($this->normalize($key), $default);
    }

    /**
     * Normalize the given key name.
     *
     * @param string $key The given key name.
     *
     * @return string
     */
    protected function normalize(string $key) : string
    {
        return str_replace('_', '-', strtolower($key));
    }

    /**
     * Get HTTP cookies.
     *
     * @return array
     */
    public function getCookies() : array
    {
        return [];
    }

    /**
     * Get the string representation of the headers.
     *
     * @return string
     */
    public function __toString() : string
    {
        $results = [];

        foreach ($this->all() as $key => $value) {
            $results[] = sprintf('%s: %s', ucwords($key, '-'), $value);
        }

        return implode("\r\n", $results);
    }
}
