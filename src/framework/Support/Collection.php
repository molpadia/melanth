<?php

namespace Melanth\Support;

use stdClass;
use Countable;
use Exception;
use ArrayAccess;
use Traversable;
use ArrayIterator;
use CachingIterator;
use JsonSerializable;
use IteratorAggregate;
use Melanth\Contracts\Support\Arrayable;
use Melanth\Contracts\Support\Jsonable;

class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * The collection items.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Create a new collection.
     *
     * @param mixed $items The collection items.
     *
     * @return void
     */
    public function __construct($items = [])
    {
        $this->items = $this->getArrayableItems($items);
    }

    /**
     * Create a new collection instance.
     *
     * @param mixed $items The collection items.
     *
     * @return static
     */
    public static function make($items = []) : Collection
    {
        return new static($items);
    }

    /**
     * Get all of the items in the collection.
     *
     * @return array
     */
    public function all() : array
    {
        return $this->items;
    }

    /**
     * Dump the collection and end the script.
     *
     * @param mixed ...$arguments The rest of th arguments.
     *
     * @return void
     */
    public function dd(...$arguments) : Collection
    {
        call_user_func_array([$this, 'dump'], $arguments);

        die(1);
    }

    /**
     * Get the items in the collection that ar not present in The collection items.
     *
     * @param mixed $items The collection items.
     *
     * @return static
     */
    public function diff($items) : Collection
    {
        return new static(array_diff($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Get the items in the collection whose keys ar not present.
     *
     * @param mixed $items The mixed items.
     *
     * @return static
     */
    public function diffKeys($items) : Collection
    {
        return new static(array_diff_key($this->items, $this->getArrayableItems($items)));
    }

    /**
     * Loop through a callback over each item.
     *
     * @param callable $callback The callback
     *
     * @return $this
     */
    public function each(callable $callback) : Collection
    {
        foreach ($this->items as $key => $item) {
            if ($callback($item, $key) === false) {
                break;
            }
        }

        return $this;
    }

    /**
     * Filter each items in the collection.
     *
     * @param callable|null $callback The callback.
     *
     * @return static
     */
    public function filter(callable $callback = null) : Collection
    {
        if ($callback) {
            return new static(Arr::where($this->items, $callback));
        }

        return new static(array_filter($this->items));
    }

    /**
     * Convert given items to arrayable values.
     *
     * @param mixed $items The give items.
     *
     * @return array
     */
    protected function getArrayableItems($items) : array
    {
        if (is_array($items)) {
            return $items;
        } elseif ($items instanceof self) {
            return $items->all();
        } elseif ($items instanceof Arrayable) {
            return $items->toArray();
        } elseif ($items instanceof Jsonable) {
            return json_decode($items->toJson(), true);
        } elseif ($items instanceof JsonSerializable) {
            return $items->jsonSerialize();
        } elseif ($items instanceof Traversable) {
            return iterator_to_array($items);
        }

        return (array) $items;
    }

    /**
     * Add an item to the collection.
     *
     * @param mixed $item The collection item.
     *
     * @return $this
     */
    public function add($item) : Collection
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * Set an item in the collection.
     *
     * @param mixed $key   The key identifier.
     * @param mixed $value The value.
     *
     * @return $this
     */
    public function set($key, $value)
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }

        return $this;
    }

    /**
     * Get an item in the collection.
     *
     * @param mixed $key     The key identifier.
     * @param mixed $default The default value if the item is not exist.
     *
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if ($this->offsetExists($key)) {
            return $this->items[$key];
        }

        return value($default);
    }

    /**
     * Determine whether an item exists in the collection.
     *
     * @param string $key The given key.
     *
     * @return bool
     */
    public function has(string $key) : bool
    {
        return $this->offsetExists($key);
    }

    /**
     * Remove an item in the collection.
     *
     * @param string|array $keys The key list.
     *
     * @return $this
     */
    public function remove($keys)
    {
        foreach ((array) $keys as $key) {
            $this->offsetUnset($key);
        }

        return $this;
    }

    /**
     * Reset the keys on the underlying array.
     *
     * @return self
     */
    public function values() : Collection
    {
        return new static(array_values($this->items));
    }

    /**
     * Get the collection of items as array.
     *
     * @return array
     */
    public function toArray()
    {
        return array_map(function ($value) {
            return $value instanceof Arrayable ? $value->toArray() : $value;
        }, $this->items);
    }

    /**
     * Get the object into json serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return array_map(function ($value) {
            if ($value instanceof JsonSerializable) {
                return $value->jsonSerialize();
            } elseif ($value instanceof Jsonable) {
                return json_decode($value->toJson(), true);
            } elseif ($value instanceof Arrayable) {
                return $value->toArray();
            }

            return $value;
        }, $this->items);
    }

    /**
     * Get the collection of items.
     *
     * @param int $options The encoded options.
     *
     * @return string
     */
    public function toJson(int $options = 0) : string
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count() : int
    {
        return count($this->items);
    }

    /**
     * Set the item at a given offset.
     *
     * @param mixed $key   The itrm key.
     * @param mixed $value The item value.
     *
     * @return void
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * Get an item at a given offset.
     *
     * @param mixed $key The item key.
     *
     * @return mixed
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param mixed $key The item key.
     *
     * @return bool
     */
    public function offsetExists($key)
    {
        return array_key_exists($key, $this->items);
    }

    /**
     * Unset the item at a given offset.
     *
     * @param string $key The key.
     *
     * @return void
     */
    public function offsetUnset($key)
    {
        unset($this->items[$key]);
    }

    /**
     * Convert the collection to its string representation.
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->toJson();
    }
}
