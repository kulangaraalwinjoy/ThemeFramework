<?php

declare(strict_types=1);

namespace YourVendor\ThemeFramework\Menu;

use YourVendor\ThemeFramework\Field\FieldType;
use YourVendor\ThemeFramework\Field\FieldValidator;
use YourVendor\ThemeFramework\Security\Capability;
use YourVendor\ThemeFramework\Security\Nonce;
use YourVendor\ThemeFramework\Storage\MenuMetaStorage;

/**
 * Handles security checks, sanitization, and saving of menu item custom fields.
 */
class MenuFieldSaver
{
    /**
     * Menu meta storage instance.
     *
     * @var MenuMetaStorage
     */
    protected MenuMetaStorage $storage;

    /**
     * Constructor.
     *
     * @param MenuMetaStorage|null $storage
     */
    public function __construct(?MenuMetaStorage $storage = null)
    {
        $this->storage = $storage ?? new MenuMetaStorage();
    }

    /**
     * Save menu custom fields for a specific menu item.
     *
     * @param int $menuId Nav menu ID.
     * @param int $menuItemDbId Nav menu item post ID.
     * @param array<string, mixed> $data Request data (e.g. $_POST).
     * @return bool
     */
    public function save(int $menuId, int $menuItemDbId, array $data): bool
    {
        if ($menuItemDbId <= 0) {
            return false;
        }

        if (!Capability::canEditThemeOptions()) {
            return false;
        }

        // Verify nonce if present in standard WordPress nav menu forms
        if (isset($data['_theme_menu_nonce']) && !Nonce::verify((string) $data['_theme_menu_nonce'], 'menu_fields')) {
            return false;
        }

        $fields = MenuFieldRegistry::all();
        if (empty($fields)) {
            return false;
        }

        $itemData = $data['_theme_menu'][$menuItemDbId] ?? [];
        $validator = new FieldValidator();

        foreach ($fields as $fieldId => $field) {
            $type = (string) ($field['type'] ?? 'text');
            $hasKey = is_array($itemData) && array_key_exists($fieldId, $itemData);

            if (!$hasKey) {
                if ($type === 'checkbox') {
                    $rawValue = 0;
                } else {
                    continue;
                }
            } else {
                $rawValue = $itemData[$fieldId];
            }

            // Validate
            if (!$validator->validate($rawValue, $field)) {
                continue;
            }

            // Sanitize
            if (!empty($field['sanitize']) && is_callable($field['sanitize'])) {
                $sanitized = call_user_func($field['sanitize'], $rawValue, $field);
            } else {
                $handler = FieldType::get($type);
                $sanitized = $handler->sanitize($rawValue, $field);
            }

            // Save or delete
            $isEmpty = $sanitized === null || $sanitized === '' || $sanitized === [];
            if ($isEmpty && $type !== 'checkbox') {
                $this->storage->delete($fieldId, $menuItemDbId);
                do_action('theme_framework_menu_field_deleted', $field, $menuItemDbId);
            } else {
                $this->storage->set($fieldId, $sanitized, $menuItemDbId);
                do_action('theme_framework_menu_field_saved', $field, $sanitized, $menuItemDbId);
            }
        }

        return true;
    }
}
