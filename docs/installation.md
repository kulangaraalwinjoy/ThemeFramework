# Installation Guide

`yourvendor/wp-theme-framework` is a standalone PHP Composer library designed to be installed into any WordPress theme.

---

## Requirements

* **PHP**: 8.1 or higher
* **WordPress**: 6.0 or higher
* **Composer**: 2.x

---

## Standard Installation

In your theme directory (or WordPress project root), run:

```bash
composer require yourvendor/wp-theme-framework
```

---

## Installing Directly from GitHub via VCS

To install this package directly from GitHub during development, add the VCS repository entry to your project's `composer.json`:

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

Then run:

```bash
composer update yourvendor/wp-theme-framework
```

---

## Theme Integration

In your theme's `functions.php`:

```php
// Load Composer autoloader
if (file_exists(get_template_directory() . '/vendor/autoload.php')) {
    require_once get_template_directory() . '/vendor/autoload.php';
}

use YourVendor\ThemeFramework\Framework;

// Boot the framework once
Framework::boot();
```

---

## Production Deployment

You do **not** need Composer installed on your live production server.

During your build or deployment step (e.g. CI/CD pipeline or local build):

1. Run:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```
2. Deploy the resulting theme folder (including the generated `vendor/` directory) to the WordPress production server.
