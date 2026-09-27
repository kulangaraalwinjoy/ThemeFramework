# WordPress Theme Framework

[![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://www.php.net/)
[![License: GPL-2.0-or-later](https://img.shields.io/badge/License-GPL--2.0--or--later-green.svg)](LICENSE)
[![Tests](https://img.shields.io/badge/Tests-Passing-brightgreen.svg)](tests/)
[![Static Analysis](https://img.shields.io/badge/PHPStan-Level%207-brightgreen.svg)](phpstan.neon.dist)

A production-ready, declarative PHP framework and Composer package for WordPress theme development.

The framework lets developers define **WHAT** they need using simple declarative configuration. It automatically handles the underlying WordPress implementation:

* **Admin UI & Rendering**
* **Saving & Loading**
* **Validation & Sanitization**
* **Nonces & Capability Checks**
* **WordPress Hooks & Lifecycle**
* **Media Library Integration (Single & Multi-Gallery)**
* **Nav Menu Item Custom Fields**
* **Post Meta & Options Storage**
* **Smart Conditional Asset Loading**
* **WordPress REST API Integration**
* **Third-Party Field Type Extensibility**

---

## Architecture Principle: One Field Engine

```text
Field Engine
    │
    ├── Post Context   ──> Post Meta Storage
    ├── Menu Context   ──> Menu Meta Storage
    └── Option Context ──> Option Storage
    │
    └── Field Types:
        ├── text
        ├── textarea
        ├── number
        ├── url
        ├── email
        ├── checkbox
        ├── select
        ├── media
        ├── gallery
        └── custom (extensible via FieldType::register())
```

There is **one** shared field engine. Field types do not know or care whether they are rendered inside a Post Meta Box, a Navigation Menu item, or a Theme Settings page. Storage adapters handle persistence according to the context.

---

## Installation

Install into your WordPress theme using Composer:

```bash
composer require yourvendor/wp-theme-framework
```

### Direct GitHub Installation (VCS)

During development or if using GitHub directly, add this to your theme's `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/kulangaraalwinjoy/ThemeFramework"
        }
    ],
    "require": {
        "yourvendor/wp-theme-framework": "dev-main"
    }
}
```

Then run `composer update yourvendor/wp-theme-framework`.

---

## Quick Start Example

Add this to your theme's `functions.php`:

```php
require_once get_template_directory() . '/vendor/autoload.php';

use YourVendor\ThemeFramework\Framework;
use YourVendor\ThemeFramework\PostType;
use YourVendor\ThemeFramework\Field;
use YourVendor\ThemeFramework\MenuField;
use YourVendor\ThemeFramework\Option;
use YourVendor\ThemeFramework\OptionPage;

// 1. Boot the framework (Idempotent)
Framework::boot();

// 2. Register Custom Post Type
PostType::register('project', [
    'label'        => 'Projects',
    'supports'     => ['title', 'editor', 'thumbnail'],
    'show_in_rest' => true,
    'menu_icon'    => 'dashicons-portfolio',
]);

// 3. Register Post Fields
Field::add('location', [
    'label'        => 'Location',
    'type'         => 'text',
    'post_type'    => 'project',
    'show_in_rest' => true,
    'validation'   => [
        'required' => true,
    ],
]);

Field::add('icon', [
    'label'         => 'Icon',
    'type'          => 'media',
    'post_type'     => 'project',
    'allowed_types' => [
        'image/png',
        'image/svg+xml',
    ],
]);

Field::add('gallery', [
    'label'     => 'Gallery',
    'type'      => 'gallery',
    'post_type' => 'project',
]);

// 4. Register Menu Item Fields
MenuField::add('is_mega_menu', [
    'label' => 'Mega Menu',
    'type'  => 'checkbox',
]);

MenuField::add('menu_icon', [
    'label' => 'Menu Icon',
    'type'  => 'media',
]);

// 5. Register Option Page
OptionPage::register('theme_settings', [
    'title'      => 'Theme Settings',
    'menu_title' => 'Theme Settings',
    'capability' => 'manage_options',
    'icon'       => 'dashicons-admin-generic',
    'position'   => 60,
]);

// 6. Register Theme Options
Option::add('custom_css', [
    'label'         => 'Custom CSS',
    'type'          => 'textarea',
    'page'          => 'theme_settings',
    'sanitize_mode' => 'css',
]);
```

---

## Template Usage

In your theme templates (e.g. `single-project.php`):

```php
use YourVendor\ThemeFramework\Field;
use YourVendor\ThemeFramework\Option;

// Retrieve post fields
$location   = Field::get('location');
$iconUrl    = Field::url('icon');
$iconData   = Field::attachment('icon');
$galleryUrls = Field::images('gallery', get_the_ID(), 'large');

// Retrieve theme option
$customCss  = Option::get('custom_css');
```

---

## Built-in Field Types

* `text`: Single-line text input.
* `textarea`: Multi-line text area (supports CSS and JS sanitization modes).
* `number`: Numeric input with min, max, step.
* `url`: URL input with strict `esc_url_raw()` sanitization.
* `email`: Email input with email validation.
* `checkbox`: Boolean toggle storing `1` or `0`.
* `select`: Single or multi-select dropdown with option whitelisting.
* `media`: Native WordPress Media modal single attachment picker. Stores attachment ID, supports MIME restrictions (`allowed_types` or `library`), and safe SVG verification.
* `gallery`: Native Media modal multi-image picker with drag-and-drop sortable thumbnail reordering. Stores array of attachment IDs.
* `custom`: Escape hatch for callable custom rendering and sanitization.

---

## Conditional Fields

Show or hide fields dynamically based on other field values:

```php
Field::add('video_url', [
    'label'      => 'Video URL',
    'type'       => 'url',
    'post_type'  => 'project',
    'conditions' => [
        [
            'field'   => 'project_type',
            'compare' => '==',
            'value'   => 'video',
        ],
    ],
]);
```

Supported operators: `==`, `!=`, `>`, `<`, `>=`, `<=`, `IN`, `NOT IN`.

---

## Development & Quality Assurance

The package includes development scripts for unit testing, coding standards, and static analysis:

```bash
# Run PHPUnit test suite (Unit & Integration tests)
composer test

# Run PHP_CodeSniffer with WordPress Coding Standards
composer lint

# Run PHPStan static analysis (Level 7)
composer analyse

# Automatically format code using phpcbf
composer format
```

---

## Documentation

Full documentation is available in the [`docs/`](docs/) directory:

* [Installation Guide](docs/installation.md)
* [Quick Start](docs/quick-start.md)
* [Custom Post Types](docs/post-types.md)
* [Field API](docs/fields.md)
* [Built-in Field Types](docs/field-types.md)
* [Media Fields](docs/media.md)
* [Gallery Fields](docs/gallery.md)
* [Menu Custom Fields](docs/menus.md)
* [Theme Options](docs/options.md)
* [Option Pages](docs/option-pages.md)
* [Validation](docs/validation.md)
* [Conditional Fields](docs/conditional-fields.md)
* [Asset Management](docs/assets.md)
* [Custom Field Types](docs/custom-field-types.md)
* [REST API Integration](docs/rest.md)
* [Extending (Hooks & Filters)](docs/extending.md)
* [Security Guide](docs/security.md)
* [Upgrading & SemVer](docs/upgrading.md)

---

## License

This package is open-source software licensed under the [GNU General Public License v2 or later](LICENSE).
