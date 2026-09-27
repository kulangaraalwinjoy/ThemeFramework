# Quick Start Guide

Get up and running with `yourvendor/wp-theme-framework` in minutes.

---

## 1. Boot the Framework

In your theme's `functions.php`:

```php
require_once get_template_directory() . '/vendor/autoload.php';

use YourVendor\ThemeFramework\Framework;
use YourVendor\ThemeFramework\PostType;
use YourVendor\ThemeFramework\Field;
use YourVendor\ThemeFramework\MenuField;
use YourVendor\ThemeFramework\Option;
use YourVendor\ThemeFramework\OptionPage;

Framework::boot();
```

---

## 2. Register a Custom Post Type

```php
PostType::register('project', [
    'label'        => 'Projects',
    'supports'     => ['title', 'editor', 'thumbnail'],
    'show_in_rest' => true,
    'menu_icon'    => 'dashicons-portfolio',
]);
```

---

## 3. Register Custom Post Fields

```php
Field::add('location', [
    'label'     => 'Location',
    'type'      => 'text',
    'post_type' => 'project',
]);

Field::add('icon', [
    'label'     => 'Icon',
    'type'      => 'media',
    'post_type' => 'project',
]);

Field::add('gallery', [
    'label'     => 'Gallery',
    'type'      => 'gallery',
    'post_type' => 'project',
]);
```

---

## 4. Register Nav Menu Custom Fields

```php
MenuField::add('is_mega_menu', [
    'label' => 'Mega Menu',
    'type'  => 'checkbox',
]);

MenuField::add('menu_icon', [
    'label' => 'Menu Icon',
    'type'  => 'media',
]);
```

---

## 5. Register Option Pages and Theme Options

```php
OptionPage::register('theme_settings', [
    'title' => 'Theme Settings',
]);

Option::add('custom_css', [
    'label' => 'Custom CSS',
    'type'  => 'textarea',
    'page'  => 'theme_settings',
]);
```

---

## 6. Access Stored Values in Templates

In your theme template (e.g. `single-project.php`):

```php
use YourVendor\ThemeFramework\Field;
use YourVendor\ThemeFramework\Option;

// Retrieve post meta
$location = Field::get('location');
$iconUrl  = Field::url('icon');
$gallery  = Field::images('gallery', get_the_ID(), 'large');

// Retrieve theme option
$customCss = Option::get('custom_css');
```
