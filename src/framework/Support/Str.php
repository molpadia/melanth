<?php

namespace Melanth\Support;

class Str
{
    /**
     * The cache of snake-cased words.
     *
     * @var string
     */
    protected static $snakeCache = [];

    /**
     * The cache of camel-cased words.
     *
     * @var array
     */
    protected static $camelCache = [];

    /**
     * The cache of studly-cased words.
     *
     * @var string
     */
    protected static $studlyCache = [];

    /**
     * Convert a value to camel case.
     *
     * @param string $value The given value.
     *
     * @return string
     */
    public static function camel(string $value) : string
    {
        if (isset(static::$camelCache[$value])) {
            return static::$camelCache[$value];
        }

        return static::$camelCache[$value] = lcfirst(static::studly($value));
    }

    /**
     * Convert a value to studly case.
     *
     * @param string $value The given value.
     *
     * @return string
     */
    public static function studly(string $value) : string
    {
        $key = $value;

        if (isset(static::$studlyCache[$key])) {
            return static::$studlyCache[$key];
        }

        $value = ucwords(str_replace(['-', '_'], ' ', $value));

        return static::$studlyCache[$key] = str_replace(' ', '', $value);
    }

    /**
     * Convert a value to snake case.
     *
     * @param string $value     The given value.
     * @param string $delimiter The delimiter splits the value.
     *
     * @return string
     */
    public static function snake(string $value , string $delimiter = '_') : string
    {
        $key = $value;

        if (isset(static::$snakeCache[$key][$delimiter])) {
            return static::$snakeCache[$key][$delimiter];
        }

        if (! ctype_lower($value)) {
            $value = preg_replace('/\s+/u', '', ucwords($value));

            $value = static::lower(preg_replace('/(.)(?=[A-Z])/u', '$1'.$delimiter, $value));
        }

        return static::$snakeCache[$key][$delimiter] = $value;
    }

    /**
     * Determine whether the value starts with a given substring.
     *
     * @param string       $haystack The haystack.
     * @param string|array $needles  The needles.
     *
     * @return bool
     */
    public static function startsWith(string $haystack, $needles) : bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the value ends with a given substring.
     *
     * @param string       $haystack The haystack.
     * @param string|array $needles  The needles.
     *
     * @return bool
     */
    public static function endsWith(string $haystack, $needles) : bool
    {
        foreach ((array) $needles as $needle) {
            if (substr($haystack, -strlen($needle)) === (string) $needle) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the string contains a given substring.
     *
     * @param string       $haystack The haystack.
     * @param string|array $needles  The needles.
     *
     * @return bool
     */
    public static function contains($haystack, $needles) : bool
    {
        foreach ((array) $needles as $needle) {
            if ($needle !== '' && mb_strpos($haystack, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Limit the number of characters in a string.
     *
     * @param string $value The given value.
     * @param int    $limit The maximum number of limit.
     * @param string $end   The replacement at the end position.
     *
     * @return string
     */
    public static function limit(string $value, int $limit = 100, $end = '...') : string
    {
        if (mb_strwidth($value, 'UTF-8') <= $limit) {
            return $value;
        }

        return rtrim(mb_strimwidth($value, 0, $limit, '', 'UTF-8')).$end;
    }

    /**
     * Convert a value to lower-case.
     *
     * @param string $value The given value.
     *
     * @return string
     */
    public static function lower(string $value) : string
    {
        return mb_strtolower($value, 'UTF-8');
    }

    /**
     * Convert a value to upper-case.
     *
     * @param string $value The given value.
     *
     * @return string
     */
    public static function upper(string $value) : string
    {
        return mb_strtoupper($value, 'UTF-8');
    }

    /**
     * Get the portion of string.
     *
     * @param string   $value  The given value.
     * @param int      $start  The start position.
     * @param int|null $length The total length.
     *
     * @return string
     */
    public static function substr(string $value, int $start = 0, int $length = null) : string
    {
        return mb_substr($value, $start, $length, 'UTF-8');
    }

    /**
     * Get the length of the given string.
     *
     * @param string $value    The given value.
     * @param string $encoding The encoding format.
     *
     * @return int
     */
    public static function length(string $value, string $encoding = null) : int
    {
        return is_null($encoding) ? mb_strlen($value) : mb_strlen($value, $encoding);
    }

    /**
     * Convert the first character to upper-case.
     *
     * @param string $value The given value.
     *
     * @return string
     */
    public static function ucfirst(string $value) : string
    {
        return static::upper(static::substr($value, 0, 1)).static::substr($value, 1);
    }

    /**
     * Parse the pattern in `class@method` style.
     *
     * @param string      $pattern The pattern.
     * @param string|null $default The default value.
     *
     * @return array
     */
    public static function parseCallback($callback, $default = null) : array
    {
        return static::contains($callback, '@') ? explode('@', $callback, 2) : [$callback, $default];
    }
}
