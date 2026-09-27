<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Support;

/**
 * String helper utility class.
 */
class Str
{
    /**
     * Convert a string to kebab-case slug.
     *
     * @param string $title
     * @param string $separator
     * @return string
     */
    public static function slug(string $title, string $separator = '-'): string
    {
        $title = preg_replace('![' . preg_quote($separator, '!') . '\s]+!u', $separator, $title);
        $title = preg_replace('![^\pL\pN' . preg_quote($separator, '!') . ']+!u', '', $title ?? '');
        $title = mb_strtolower($title ?? '', 'UTF-8');
        return trim($title, $separator);
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function startsWith(string $haystack, string $needle): bool
    {
        return str_starts_with($haystack, $needle);
    }

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param string $haystack
     * @param string $needle
     * @return bool
     */
    public static function endsWith(string $haystack, string $needle): bool
    {
        return str_ends_with($haystack, $needle);
    }

    /**
     * Convert a string to StudlyCase.
     *
     * @param string $value
     * @return string
     */
    public static function studly(string $value): string
    {
        $value = ucwords(str_replace(['-', '_'], ' ', $value));
        return str_replace(' ', '', $value);
    }

    /**
     * Convert a string to camelCase.
     *
     * @param string $value
     * @return string
     */
    public static function camel(string $value): string
    {
        return lcfirst(static::studly($value));
    }
}
