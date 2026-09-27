<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Support;

/**
 * Handles conditional field rules evaluation and serialization.
 */
class Conditions
{
    /**
     * Evaluate a set of conditions against a value retriever callback.
     *
     * @param array<int, array<string, mixed>> $conditions
     * @param callable(string): mixed $getValueCallback
     * @return bool
     */
    public static function evaluate(array $conditions, callable $getValueCallback): bool
    {
        if (empty($conditions)) {
            return true;
        }

        foreach ($conditions as $condition) {
            $field = (string) ($condition['field'] ?? '');
            if ($field === '') {
                continue;
            }

            $operator = (string) ($condition['compare'] ?? '==');
            $expected = $condition['value'] ?? null;
            $actual = $getValueCallback($field);

            if (!self::compare($actual, $operator, $expected)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Compare actual and expected values using the given operator.
     *
     * @param mixed $actual
     * @param string $operator
     * @param mixed $expected
     * @return bool
     */
    public static function compare(mixed $actual, string $operator, mixed $expected): bool
    {
        return match (strtoupper($operator)) {
            '==' => (string) $actual === (string) $expected,
            '!=' => (string) $actual !== (string) $expected,
            '>' => is_numeric($actual) && is_numeric($expected) && $actual > $expected,
            '<' => is_numeric($actual) && is_numeric($expected) && $actual < $expected,
            '>=' => is_numeric($actual) && is_numeric($expected) && $actual >= $expected,
            '<=' => is_numeric($actual) && is_numeric($expected) && $actual <= $expected,
            'IN' => in_array($actual, (array) $expected, false),
            'NOT IN' => !in_array($actual, (array) $expected, false),
            default => (string) $actual === (string) $expected,
        };
    }

    /**
     * Convert conditions array to a JSON attribute for DOM elements.
     *
     * @param array<int, array<string, mixed>> $conditions
     * @return string
     */
    public static function toJsonAttribute(array $conditions): string
    {
        if (empty($conditions)) {
            return '';
        }

        $json = wp_json_encode($conditions);
        return $json !== false ? esc_attr($json) : '';
    }
}
