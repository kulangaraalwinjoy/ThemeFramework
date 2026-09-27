# Menu Item Custom Fields

The `MenuField` API adds custom fields to WordPress navigation menu items without replacing or breaking WordPress's native menu management system.

---

## Example Usage

```php
use YourVendor\ThemeFramework\MenuField;

MenuField::add('is_mega_menu', [
    'label'          => 'Mega Menu',
    'type'           => 'checkbox',
    'checkbox_label' => 'Enable mega menu layout for this column',
]);

MenuField::add('menu_icon', [
    'label'         => 'Menu Icon',
    'type'          => 'media',
    'allowed_types' => [
        'image/png',
        'image/svg+xml',
    ],
]);
```

---

## Architecture & Integration

* **Native Hooks**: Hooked into WordPress core's `wp_nav_menu_item_custom_fields` (WP 5.4+) and `wp_update_nav_menu_item`.
* **Shared Field Engine**: Reuses the exact same field type rendering, validation, and sanitization logic as post fields.
* **Storage**: Stored in WordPress post meta for the `nav_menu_item` post using the controlled key prefix `_theme_menu_{id}`.
* **Security**: Enforces nonces and verifies the `edit_theme_options` user capability.

---

## Querying Menu Fields in Custom Walkers or Filters

### Using `wp_nav_menu_objects` Filter

```php
add_filter('wp_nav_menu_objects', function (array $items) {
    foreach ($items as $item) {
        $isMega = MenuField::get('is_mega_menu', $item->ID);
        $iconUrl = MenuField::url('menu_icon', $item->ID);

        if ($isMega) {
            $item->classes[] = 'menu-item-mega';
        }

        if ($iconUrl) {
            $item->title = sprintf(
                '<img src="%s" class="menu-icon" alt="" /> %s',
                esc_url($iconUrl),
                $item->title
            );
        }
    }
    return $items;
});
```

### Public API Methods

* `MenuField::get(string $id, int $menuItemId, mixed $default = null): mixed`
* `MenuField::set(string $id, int $menuItemId, mixed $value): bool`
* `MenuField::delete(string $id, int $menuItemId): bool`
* `MenuField::url(string $id, int $menuItemId, string $size = 'full'): ?string`
