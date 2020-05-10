<?php

use Melanth\Support\Arr;

if (! function_exists('value')) {
    /**
     * Return the default value of the given value.
     *
     * @param mixed $value The default value.
     *
     * @return mixed
     */
    function value($value)
    {
        return $value instanceof Closure ? $value() : $value;
    }
}

if (! function_exists('data_get')) {
    /**
     * Get an item of an array by using dot notation.
     *
     * @param mixed            $items   The target items.
     * @param string|array|int $key     The given key.
     * @param mixed            $default The default value.
     *
     * @return array
     */
    function data_get($items, $key, $default = null)
    {
        if (is_null($key)) {
            return $items;
        }

        $key = is_array($key) ? $key : explode('.', $key);

        while (! is_null($segment = array_shift($key))) {
            if (Arr::accessible($items) && Arr::exists($items, $segment)) {
                $items = $items[$segment];
            } elseif (is_object($items) && isset($items->{$segment})) {
                $items = $itmes->{$segment};
            } else {
                return value($default);
            }
        }

        return $items;
    }
}
