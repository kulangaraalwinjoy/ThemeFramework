<?php

declare(strict_types=1);

namespace YourVendor\ThemeFramework\Menu;

use YourVendor\ThemeFramework\Field\FieldRenderer;
use YourVendor\ThemeFramework\Field\FieldType;
use YourVendor\ThemeFramework\Security\Nonce;
use YourVendor\ThemeFramework\Storage\MenuMetaStorage;

/**
 * Public API Facade for menu item custom fields.
 */
class MenuField
{
    /**
     * Menu meta storage instance.
     *
     * @var MenuMetaStorage|null
     */
    protected static ?MenuMetaStorage $storage = null;

    /**
     * Get storage instance.
     *
     * @return MenuMetaStorage
     */
    public static function getStorage(): MenuMetaStorage
    {
        if (self::$storage === null) {
            self::$storage = new MenuMetaStorage();
        }

        return self::$storage;
    }

    /**
     * Set custom storage instance.
     *
     * @param MenuMetaStorage|null $storage
     * @return void
     */
    public static function setStorage(?MenuMetaStorage $storage): void
    {
        self::$storage = $storage;
    }

    /**
     * Register a new menu field.
     *
     * @param string $id
     * @param array<string, mixed> $config
     * @return void
     */
    public static function add(string $id, array $config): void
    {
        MenuFieldRegistry::add($id, $config);
    }

    /**
     * Retrieve a menu field's stored value.
     *
     * @param string $id
     * @param int $menuItemId
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $id, int $menuItemId, mixed $default = null): mixed
    {
        $value = self::getStorage()->get($id, $menuItemId, $default);
        $field = MenuFieldRegistry::get($id) ?? ['id' => $id];

        /**
         * Filter retrieved menu field value.
         *
         * @param mixed $value
         * @param array $field
         * @param int $menuItemId
         */
        return apply_filters('theme_framework_menu_field_value', $value, $field, $menuItemId);
    }

    /**
     * Update a menu field's value.
     *
     * @param string $id
     * @param int $menuItemId
     * @param mixed $value
     * @return bool
     */
    public static function set(string $id, int $menuItemId, mixed $value): bool
    {
        $field = MenuFieldRegistry::get($id);
        if ($field) {
            $type = (string) ($field['type'] ?? 'text');
            if (FieldType::has($type)) {
                $handler = FieldType::get($type);
                $value = $handler->sanitize($value, $field);
            }
        }

        return self::getStorage()->set($id, $value, $menuItemId);
    }

    /**
     * Delete a menu field's value.
     *
     * @param string $id
     * @param int $menuItemId
     * @return bool
     */
    public static function delete(string $id, int $menuItemId): bool
    {
        return self::getStorage()->delete($id, $menuItemId);
    }

    /**
     * Retrieve URL for a menu media field attachment.
     *
     * @param string $id
     * @param int $menuItemId
     * @param string $size
     * @return string|null
     */
    public static function url(string $id, int $menuItemId, string $size = 'full'): ?string
    {
        $attachmentId = self::get($id, $menuItemId);
        if (empty($attachmentId) || !is_numeric($attachmentId)) {
            return null;
        }

        $url = wp_get_attachment_image_url((int) $attachmentId, $size);
        if ($url) {
            return (string) $url;
        }

        $fallback = wp_get_attachment_url((int) $attachmentId);
        return $fallback ? (string) $fallback : null;
    }

    /**
     * Hook callback for rendering custom fields inside wp_nav_menu_item_custom_fields.
     *
     * @param int|string $itemId
     * @param mixed $item
     * @param int $depth
     * @param mixed $args
     * @return void
     */
    public static function renderItemFields(mixed $itemId, mixed $item = null, int $depth = 0, mixed $args = null): void
    {
        $fields = MenuFieldRegistry::all();
        if (empty($fields)) {
            return;
        }

        $itemIdInt = (int) $itemId;
        $renderer = new FieldRenderer();

        // Output nonce once per menu item row
        echo '<div class="theme-framework-menu-fields" style="clear: both; margin: 10px 0;">';
        Nonce::field('menu_fields', '_theme_menu_nonce', false, true);

        foreach ($fields as $fieldId => $field) {
            $value = self::get($fieldId, $itemIdInt, $field['default'] ?? null);
            $inputName = sprintf('_theme_menu[%d][%s]', $itemIdInt, $fieldId);

            // Menu item container formatting
            echo '<div class="theme-framework-menu-field-wrap description description-wide" style="margin-top: 5px;">';
            $renderer->renderRow($field, $value, $inputName, $itemIdInt);
            echo '</div>';
        }

        echo '</div>';
    }

    /**
     * Hook callback for saving menu custom fields on wp_update_nav_menu_item.
     *
     * @param int $menuId
     * @param int $menuItemDbId
     * @param mixed $args
     * @return void
     */
    public static function saveItemFields(int $menuId, int $menuItemDbId, mixed $args = null): void
    {
        $saver = new MenuFieldSaver(self::getStorage());
        $saver->save($menuId, $menuItemDbId, $_POST); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    }
}
