# Custom Post Types

The framework provides a clean, declarative interface for registering WordPress Custom Post Types while preserving 100% native compatibility with `register_post_type()`.

---

## Registration

```php
use KulangaraAlwinJoy\ThemeFramework\PostType;

PostType::register('project', [
    'label'          => 'Projects',
    'singular_label' => 'Project',

    'supports' => [
        'title',
        'editor',
        'thumbnail',
        'excerpt',
    ],

    'public'       => true,
    'show_ui'      => true,
    'show_in_rest' => true,
    'has_archive'  => true,

    'rewrite' => [
        'slug' => 'projects',
    ],

    'menu_icon' => 'dashicons-portfolio',
]);
```

### Supported Arguments

All arguments accepted by WordPress's [`register_post_type()`](https://developer.wordpress.org/reference/functions/register_post_type/) are supported directly, including:

* `label` (string): General name for the post type.
* `singular_label` (string): Singular name (used to generate default labels).
* `labels` (array): Custom label overrides.
* `supports` (array): Features supported (e.g. `'title'`, `'editor'`, `'thumbnail'`, `'custom-fields'`).
* `public` (bool): Whether the post type is public.
* `show_ui` (bool): Whether to generate a default UI for managing this post type in the admin.
* `show_in_rest` (bool): Exposes post type in the Block Editor and REST API.
* `has_archive` (bool|string): Enables post type archives.
* `rewrite` (bool|array): Triggers the handling of rewrites for this post type.
* `menu_icon` (string): Dashicon slug or icon URL.

---

## Post Type Specific Assets

You can associate admin and frontend stylesheets and scripts with a post type. The framework only enqueues them when the post type is being viewed or edited.

```php
PostType::assets('project', [
    'admin' => [
        'css' => [
            'project-admin-style',
        ],
        'js' => [
            'project-admin-script',
        ],
    ],

    'frontend' => [
        'css' => [
            'project-frontend-style',
        ],
        'js' => [
            'project-frontend-script',
        ],
    ],
]);
```
