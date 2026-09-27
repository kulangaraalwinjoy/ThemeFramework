# Theme Options API

The `Option` API allows theme developers to define, store, and query global theme options cleanly without making raw `get_option()` calls throughout template code.

---

## Adding Theme Options

```php
use YourVendor\ThemeFramework\Option;

Option::add('site_logo', [
    'label' => 'Site Logo',
    'type'  => 'media',
    'page'  => 'theme_settings',
]);

Option::add('contact_phone', [
    'label' => 'Contact Phone',
    'type'  => 'text',
    'page'  => 'theme_settings',
]);

Option::add('custom_css', [
    'label'         => 'Custom CSS',
    'type'          => 'textarea',
    'page'          => 'theme_settings',
    'sanitize_mode' => 'css',
]);
```

---

## Querying Options in Theme Templates

### `Option::get($id, $default = null)`
Retrieves the saved option value.

```php
$phone = Option::get('contact_phone', '+1-800-555-0100');
```

### `Option::set($id, $value)`
Updates the saved option, running sanitization automatically.

```php
Option::set('contact_phone', '+1-800-555-0199');
```

### `Option::delete($id)`
Deletes the option from the database.

```php
Option::delete('contact_phone');
```

### `Option::url($id, $size = 'full')`
Convenience helper for media options to retrieve the attachment URL directly.

```php
$logoUrl = Option::url('site_logo', 'medium');
```

### `Option::attachment($id)`
Retrieves full attachment metadata for a media option.

```php
$logoData = Option::attachment('site_logo');
```

---

## Storage Details

Options are stored in the WordPress `wp_options` table using the controlled key prefix:
`_theme_option_{id}`.
Raw option names are encapsulated behind the clean `Option::get()` and `Option::set()` facades.
