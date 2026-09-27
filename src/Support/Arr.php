<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Support;

/**
 * Array helper utility class.
 */
class Arr
{
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param array<string|int, mixed> $array
     * @param string|int|null $key
     * @param mixed $default
     * @return mixed
     */
    public static function get(array $array, string|int|null $key, mixed $default = null): mixed
    {
        if ($key === null) {
            return $array;
        }

        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        if (!is_string($key) || !str_contains($key, '.')) {
            return $array[$key] ?? $default;
        }

        foreach (explode('.', $key) as $segment) {
            if (is_array($array) && array_key_exists($segment, $array)) {
                $array = $array[$segment];
            } else {
                return $default;
            }
        }

        return $array;
    }

    /**
     * Determine if an item exists in an array using "dot" notation.
     *
     * @param array<string|int, mixed> $array
     * @param string|int $key
     * @return bool
     */
    public static function has(array $array, string|int $key): bool
    {
        if (array_key_exists($key, $array)) {
            return true;
        }

        if (!is_string($key) || !str_contains($key, '.')) {
            return false;
        }

        $current = $array;
        foreach (explode('.', $key) as $segment) {
            if (is_array($current) && array_key_exists($segment, $current)) {
                $current = $current[$segment];
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Wrap the given value in an array if it's not already one.
     *
     * @param mixed $value
     * @return array<mixed>
     */
    public static function wrap(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        return is_array($value) ? $value : [$value];
    }

    /**
     * Get a subset of the items from the given array.
     *
     * @param array<string, mixed> $array
     * @param array<string>|string $keys
     * @return array<string, mixed>
     */
    public static function only(array $array, array|string $keys): array
    {
        return array_intersect_key($array, array_flip((array) $keys));
    }

    /**
     * Get all of the given array except for a specified array of keys.
     *
     * @param array<string, mixed> $array
     * @param array<string>|string $keys
     * @return array<string, mixed>
     */
    public static function except(array $array, array|string $keys): array
    {
        return array_diff_key($array, array_flip((array) $keys));
    }
}
