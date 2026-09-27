# Conditional Fields

The framework features an automated conditional visibility engine that displays or hides fields in the admin UI dynamically using client-side JavaScript, while authoritative server-side evaluation protects against orphaned or hidden data saves.

---

## Defining Conditions

Add a `conditions` array to your field definition:

```php
use KulangaraAlwinJoy\ThemeFramework\Field;

// Master Field
Field::add('media_type', [
    'label'     => 'Media Type',
    'type'      => 'select',
    'post_type' => 'project',
    'choices'   => [
        'image' => 'Image Gallery',
        'video' => 'Video Embed',
    ],
    'default'   => 'image',
]);

// Dependent Field (shown only when media_type == video)
Field::add('video_url', [
    'label'      => 'Video Embed URL',
    'type'       => 'url',
    'post_type'  => 'project',
    'conditions' => [
        [
            'field'   => 'media_type',
            'compare' => '==',
            'value'   => 'video',
        ],
    ],
]);
```

---

## Supported Operators

| Operator | Meaning | Example |
|---|---|---|
| `==` | Equals (string/loose equality) | `['field' => 'type', 'compare' => '==', 'value' => 'custom']` |
| `!=` | Not equals | `['field' => 'format', 'compare' => '!=', 'value' => 'standard']` |
| `>` | Greater than (numeric) | `['field' => 'rooms', 'compare' => '>', 'value' => 3]` |
| `<` | Less than (numeric) | `['field' => 'score', 'compare' => '<', 'value' => 50]` |
| `>=` | Greater than or equal | `['field' => 'age', 'compare' => '>=', 'value' => 18]` |
| `<=` | Less than or equal | `['field' => 'rank', 'compare' => '<=', 'value' => 10]` |
| `IN` | Value in array | `['field' => 'tag', 'compare' => 'IN', 'value' => ['news', 'event']]` |
| `NOT IN` | Value not in array | `['field' => 'tag', 'compare' => 'NOT IN', 'value' => ['archive']]` |

---

## Multiple Conditions

When multiple conditions are defined, all conditions must evaluate to `true` (logical AND):

```php
Field::add('special_offer', [
    'label'      => 'Special Offer Details',
    'type'       => 'textarea',
    'post_type'  => 'product',
    'conditions' => [
        [
            'field'   => 'is_on_sale',
            'compare' => '==',
            'value'   => '1',
        ],
        [
            'field'   => 'stock_level',
            'compare' => '>',
            'value'   => 0,
        ],
    ],
]);
```

---

## How It Works

1. **Admin DOM**: The field renderer injects JSON condition metadata onto the field container via the `data-conditions` HTML attribute.
2. **JavaScript Engine**: `conditions.js` scans the page on load and listens for change and input events on all dependency fields, showing or hiding dependent rows smoothly.
3. **Server-Side Safety**: During saving, `FieldSaver` evaluates the conditions against the request payload and storage to ensure hidden fields are not erroneously populated.
