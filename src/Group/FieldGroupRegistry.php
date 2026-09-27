<?php

declare(strict_types=1);

namespace YourVendor\ThemeFramework\Group;

use InvalidArgumentException;
use YourVendor\ThemeFramework\Field\FieldRegistry;

/**
 * Registry for Meta Box Field Groups.
 */
class FieldGroupRegistry
{
    /**
     * Registered field groups.
     *
     * @var array<string, array<string, mixed>>
     */
    protected static array $groups = [];

    /**
     * Add a new field group.
     *
     * @param string $id Unique group slug.
     * @param array<string, mixed> $config Group configuration array.
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     */
    public static function add(string $id, array $config): array
    {
        $id = trim($id);
        if ($id === '') {
            throw new InvalidArgumentException('Field group ID cannot be empty.');
        }

        if (empty($config['title'])) {
            throw new InvalidArgumentException(sprintf('Field group "%s" is missing a title.', $id));
        }

        // Normalize post types
        $postTypes = [];
        if (!empty($config['post_types']) && is_array($config['post_types'])) {
            $postTypes = array_values(array_map('strval', $config['post_types']));
        } elseif (!empty($config['post_type'])) {
            $postTypes = [(string) $config['post_type']];
        }

        $group = array_merge([
            'id' => $id,
            'title' => '',
            'post_types' => $postTypes,
            'context' => 'normal',
            'priority' => 'default',
            'fields' => [],
        ], $config);

        $group['id'] = $id;
        $group['post_types'] = $postTypes;

        // Register nested fields in the central FieldRegistry so they reuse the unified field engine
        $nestedFields = (array) ($config['fields'] ?? []);
        foreach ($nestedFields as $fieldId => $fieldConfig) {
            if (empty($fieldConfig['post_types']) && empty($fieldConfig['post_type'])) {
                $fieldConfig['post_types'] = $postTypes;
            }
            $fieldConfig['group'] = $id;
            FieldRegistry::add((string) $fieldId, $fieldConfig);
        }

        /**
         * Filter registered field group definition.
         *
         * @param array $group
         */
        $group = (array) apply_filters('theme_framework_field_group_definition', $group);

        self::$groups[$id] = $group;
        return $group;
    }

    /**
     * Retrieve all registered groups.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return self::$groups;
    }

    /**
     * Retrieve a specific group.
     *
     * @param string $id
     * @return array<string, mixed>|null
     */
    public static function get(string $id): ?array
    {
        return self::$groups[$id] ?? null;
    }

    /**
     * Retrieve groups for a specific post type.
     *
     * @param string $postType
     * @return array<string, array<string, mixed>>
     */
    public static function forPostType(string $postType): array
    {
        $result = [];
        foreach (self::$groups as $id => $group) {
            $pts = (array) ($group['post_types'] ?? []);
            if (in_array('*', $pts, true) || in_array($postType, $pts, true)) {
                $result[$id] = $group;
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
        self::$groups = [];
    }
}
