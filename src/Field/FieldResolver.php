<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Field;

/**
 * Resolves fields registered for given post types, screens, or contexts.
 */
class FieldResolver
{
    /**
     * Resolve all fields registered for a given post type.
     *
     * @param string $postType
     * @return array<string, array<string, mixed>>
     */
    public static function forPostType(string $postType): array
    {
        return FieldRegistry::forPostType($postType);
    }

    /**
     * Determine if a set of fields contains a given field type.
     *
     * @param array<string, array<string, mixed>> $fields
     * @param string|array<string> $types
     * @return bool
     */
    public static function hasFieldType(array $fields, string|array $types): bool
    {
        $types = (array) $types;
        foreach ($fields as $field) {
            $type = (string) ($field['type'] ?? 'text');
            if (in_array($type, $types, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if any field in the given set has conditional rules.
     *
     * @param array<string, array<string, mixed>> $fields
     * @return bool
     */
    public static function hasConditions(array $fields): bool
    {
        foreach ($fields as $field) {
            if (!empty($field['conditions'])) {
                return true;
            }
        }

        return false;
    }
}
