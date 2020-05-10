<?php

namespace Melanth\Foundation;

use ArrayAccess;
use Melanth\Support\Arr;

class Config implements ArrayAccess
{
    /**
     * The configuration items.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Create a new configuration instance.
     *
     * @param array $items The configuration items.
     *
     * @return void
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Set an item to the configuration.
     *
     * @param array|string $key   The key name.
     * @param mixed        $value The item value.
     *
     * @return void
     */
    public function set($key, $value = null) : void
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            Arr::set($this->items, $key, $value);
        }
    }

    /**
     * Get an item from the configuration.
     *
     * @param string      $key     The key name.
     * @param string|null $default The default value.
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return Arr::get($this->items, $key, $default);
    }

    /**
     * Determine whether key exists in the configuration.
     *
     * @param string $key The key name.
     *
     * @return bool
     */
    public function has(string $key) : bool
    {
        return Arr::has($this->items, $key);
    }

    /**
     * Get all items from the configuration.
     *
     * @return array
     */
    public function all() : array
    {
        return $this->items;
    }

    /**
     * Set an item to the configuration.
     *
     * @param string $key   The key name.
     * @param mixed  $value The item value.
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Get an item from the configuration.
     *
     * @param string $key The key name.
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Determine whether key exists in the configuration.
     *
     * @param string $key The key name.
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * Unset a configuration item.
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        $this->set($key, null);
    }
}
