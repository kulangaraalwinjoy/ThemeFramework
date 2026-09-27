# Asset Management

The framework includes a high-performance Asset Manager designed to eliminate admin asset bloat. Scripts and styles (such as the WordPress Media modal and Sortable libraries) are loaded **only** on screens where relevant fields are present.

---

## Public Assets API

```php
use KulangaraAlwinJoy\ThemeFramework\Assets;

// Registering assets
Assets::registerStyle('my-admin-css', get_template_directory_uri() . '/css/admin.css');
Assets::registerScript('my-admin-js', get_template_directory_uri() . '/js/admin.js', ['jquery']);

// Enqueuing assets
Assets::enqueueStyle('my-admin-css');
Assets::enqueueScript('my-admin-js');
```

---

## Field-Specific Assets

You can attach stylesheets and scripts to an individual field definition. The framework loads them only when editing an object containing that field:

```php
Field::add('color_picker_field', [
    'label'     => 'Brand Color',
    'type'      => 'text',
    'post_type' => 'project',
    'assets'    => [
        'admin' => [
            'css' => [
                'wp-color-picker',
            ],
            'js' => [
                'wp-color-picker',
                'my-color-picker-init',
            ],
        ],
    ],
]);
```

---

## Post Type Assets

You can associate assets with a post type for both admin screens and frontend single view screens:

```php
use KulangaraAlwinJoy\ThemeFramework\PostType;

PostType::assets('project', [
    'admin' => [
        'css' => ['project-admin'],
        'js'  => ['project-admin'],
    ],

    'frontend' => [
        'css' => ['project-single'],
        'js'  => ['project-single'],
    ],
]);
```

---

## Intelligent Screen-Aware Loading

The framework automatically determines:
* Is the user on a post edit screen? Only load assets if fields are registered for that post type.
* Does any field on the screen use `media` or `gallery`? Only then does it call `wp_enqueue_media()`.
* Does any field use conditional visibility? Only then does it load `conditions.js`.
* Is the user on `nav-menus.php`? Only load menu assets if menu fields have been registered.
