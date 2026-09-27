# Extending the Framework (Hooks & Filters)

The framework exposes WordPress actions and filters at key lifecycle stages to allow customizations.

---

## Action Hooks

### `theme_framework_loaded`
Fires immediately after the framework has booted and all core field types and hooks are registered.

```php
add_action('theme_framework_loaded', function () {
    // Custom bootstrap logic
});
```

### `theme_framework_field_saved`
Fires whenever a post field is successfully validated, sanitized, and saved.

```php
add_action('theme_framework_field_saved', function (array $field, mixed $value, int $postId) {
    if ($field['id'] === 'location') {
        // Trigger geocoding or cache flush
    }
}, 10, 3);
```

### `theme_framework_field_deleted`
Fires when a field's stored value is removed.

```php
add_action('theme_framework_field_deleted', function (array $field, int $postId) {
    // Clean up dependent resources
}, 10, 2);
```

### `theme_framework_before_field_render` / `theme_framework_after_field_render`
Fires immediately before and after rendering any field control in the admin.

```php
add_action('theme_framework_before_field_render', function (array $field, mixed $value, mixed $objectId) {
    // Inject custom HTML before field
}, 10, 3);
```

### `theme_framework_menu_field_saved` / `theme_framework_menu_field_deleted`
Fires when nav menu item fields are saved or deleted.

---

## Filter Hooks

### `theme_framework_field_definition`
Filter any post field configuration array before it is stored in the registry.

```php
add_filter('theme_framework_field_definition', function (array $field) {
    // Add default attributes or modify properties
    return $field;
});
```

### `theme_framework_field_value`
Filter the value returned by `Field::get()`.

```php
add_filter('theme_framework_field_value', function (mixed $value, array $field, ?int $postId) {
    if ($field['id'] === 'location' && is_string($value)) {
        return strtoupper($value);
    }
    return $value;
}, 10, 3);
```

### `theme_framework_menu_field_value`
Filter the value returned by `MenuField::get()`.

### `theme_framework_option_value`
Filter the value returned by `Option::get()`.

### `theme_framework_post_type_args`
Filter arguments passed to `register_post_type()`.

### `theme_framework_assets_url`
Customize the base URL where the framework's CSS and JS assets are located.

```php
add_filter('theme_framework_assets_url', function (string $url) {
    return 'https://cdn.example.com/theme-framework/assets';
});
```
