# Option Pages

The `OptionPage` API registers top-level and submenu administration pages in WordPress that integrate with the WordPress Settings API.

---

## Registering Top-Level Option Pages

```php
use KulangaraAlwinJoy\ThemeFramework\OptionPage;

OptionPage::register('theme_settings', [
    'title'      => 'Theme Settings',
    'menu_title' => 'Theme Settings',
    'capability' => 'manage_options',
    'icon'       => 'dashicons-admin-generic',
    'position'   => 60,
]);
```

---

## Registering Submenu Pages

To register an option page under an existing menu (e.g. Appearance / `themes.php` or Settings / `options-general.php`):

```php
OptionPage::register('theme_typography', [
    'title'      => 'Typography Settings',
    'menu_title' => 'Typography',
    'parent'     => 'themes.php',
    'capability' => 'edit_theme_options',
]);
```

---

## Configuration Options

| Key | Type | Default | Description |
|---|---|---|---|
| `title` | `string` | *(required)* | Page heading title. |
| `menu_title` | `string` | Same as `title` | Label shown in the admin navigation sidebar. |
| `capability` | `string` | `'manage_options'` | User capability required to view and save settings. |
| `icon` | `string` | `'dashicons-admin-generic'` | Dashicons icon class or image URL. |
| `position` | `int` | `60` | Sidebar menu order position. |
| `parent` | `string\|null` | `null` | Parent menu slug for submenus. |
| `sections` | `array` | `['default' => ...]` | Grouping sections for the page. |

---

## Custom Sections

You can organize options on a page into multiple sections:

```php
OptionPage::register('theme_settings', [
    'title' => 'Theme Settings',
    'sections' => [
        'general' => [
            'id'    => 'general',
            'title' => 'General Settings',
        ],
        'social' => [
            'id'    => 'social',
            'title' => 'Social Media Links',
        ],
    ],
]);

Option::add('facebook', [
    'label'   => 'Facebook URL',
    'type'    => 'url',
    'page'    => 'theme_settings',
    'section' => 'social',
]);
```
