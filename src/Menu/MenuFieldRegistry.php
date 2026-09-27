<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Menu;

use InvalidArgumentException;
use KulangaraAlwinJoy\ThemeFramework\Field\FieldType;

/**
 * Registry for menu item custom fields.
 */
class MenuFieldRegistry
{
    /**
     * Registered menu fields.
     *
     * @var array<string, array<string, mixed>>
     */
    protected static array $fields = [];

    /**
     * Add and validate a menu field definition.
     *
     * @param string $id
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     */
    public static function add(string $id, array $config): array
    {
        $id = trim($id);
        if ($id === '') {
            throw new InvalidArgumentException('Menu field ID cannot be empty.');
        }

        if (empty($config['label'])) {
            throw new InvalidArgumentException(sprintf('Menu field "%s" is missing a label.', $id));
        }

        $type = (string) ($config['type'] ?? 'text');
        if (!FieldType::has($type)) {
            throw new InvalidArgumentException(sprintf('Unknown field type: "%s".', $type));
        }

        $allowedTypes = $config['allowed_types'] ?? $config['library'] ?? [];

        $field = array_merge([
            'id' => $id,
            'label' => '',
            'type' => 'text',
            'description' => '',
            'context' => 'menu',
            'validation' => [],
            'conditions' => [],
            'default' => null,
            'allowed_types' => (array) $allowedTypes,
            'library' => (array) $allowedTypes,
            'attributes' => [],
        ], $config);

        $field['id'] = $id;

        /**
         * Filter menu field definition.
         *
         * @param array $field
         */
        $field = (array) apply_filters('theme_framework_menu_field_definition', $field);

        self::$fields[$id] = $field;

        return $field;
    }

    /**
     * Get a menu field definition.
     *
     * @param string $id
     * @return array<string, mixed>|null
     */
    public static function get(string $id): ?array
    {
        return self::$fields[$id] ?? null;
    }

    /**
     * Check if a menu field is registered.
     *
     * @param string $id
     * @return bool
     */
    public static function has(string $id): bool
    {
        return isset(self::$fields[$id]);
    }

    /**
     * Get all registered menu fields.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return self::$fields;
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
