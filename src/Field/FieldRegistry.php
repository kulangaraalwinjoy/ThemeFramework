<?php

declare(strict_types=1);

namespace YourVendor\ThemeFramework\Field;

use InvalidArgumentException;
use YourVendor\ThemeFramework\Support\Arr;

/**
 * Central registry for all configured post and shared fields.
 */
class FieldRegistry
{
    /**
     * Registered fields storage.
     *
     * @var array<string, array<string, mixed>>
     */
    protected static array $fields = [];

    /**
     * Add and validate a field definition.
     *
     * @param string $id
     * @param array<string, mixed> $config
     * @return array<string, mixed> Normalized field configuration.
     * @throws InvalidArgumentException
     */
    public static function add(string $id, array $config): array
    {
        $id = trim($id);
        if ($id === '') {
            throw new InvalidArgumentException('Field ID cannot be empty.');
        }

        if (empty($config['label'])) {
            throw new InvalidArgumentException(sprintf('Field "%s" is missing a label.', $id));
        }

        $type = (string) ($config['type'] ?? 'text');
        if (!FieldType::has($type)) {
            throw new InvalidArgumentException(sprintf('Unknown field type: "%s".', $type));
        }

        // Normalize post types
        $postTypes = [];
        if (!empty($config['post_types']) && is_array($config['post_types'])) {
            $postTypes = array_values(array_map('strval', $config['post_types']));
        } elseif (!empty($config['post_type'])) {
            $postTypes = [(string) $config['post_type']];
        }

        // Normalize allowed types / library alias
        $allowedTypes = $config['allowed_types'] ?? $config['library'] ?? [];

        $field = array_merge([
            'id' => $id,
            'label' => '',
            'type' => 'text',
            'description' => '',
            'post_types' => $postTypes,
            'context' => 'post',
            'validation' => [],
            'conditions' => [],
            'default' => null,
            'show_in_rest' => false,
            'allowed_types' => (array) $allowedTypes,
            'library' => (array) $allowedTypes,
            'attributes' => [],
            'assets' => [],
        ], $config);

        $field['id'] = $id;
        $field['post_types'] = $postTypes;

        /**
         * Filter the field definition before storing in registry.
         *
         * @param array $field
         */
        $field = (array) apply_filters('theme_framework_field_definition', $field);

        self::$fields[$id] = $field;

        return $field;
    }

    /**
     * Retrieve a field definition by its ID.
     *
     * @param string $id
     * @return array<string, mixed>|null
     */
    public static function get(string $id): ?array
    {
        return self::$fields[$id] ?? null;
    }

    /**
     * Check if a field is registered.
     *
     * @param string $id
     * @return bool
     */
    public static function has(string $id): bool
    {
        return isset(self::$fields[$id]);
    }

    /**
     * Retrieve all registered fields.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return self::$fields;
    }

    /**
     * Retrieve all fields registered for a given post type.
     *
     * @param string $postType
     * @return array<string, array<string, mixed>>
     */
    public static function forPostType(string $postType): array
    {
        $result = [];
        foreach (self::$fields as $id => $field) {
            $pts = (array) ($field['post_types'] ?? []);
            if (in_array('*', $pts, true) || in_array($postType, $pts, true)) {
                $result[$id] = $field;
            }
        }

        return $result;
    }

    /**
     * Reset registry.
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$fields = [];
    }
}
