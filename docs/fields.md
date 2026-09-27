# Field API

The Field API provides a unified mechanism for registering, displaying, saving, and reading post metadata.

---

## Adding Fields

```php
use YourVendor\ThemeFramework\Field;

Field::add('location', [
    'label'        => 'Location',
    'type'         => 'text',
    'post_type'    => 'project',
    'description'  => 'Enter city and country.',
    'show_in_rest' => true,
]);
```

### Targeting Post Types

You can attach a field to a single post type:

```php
'post_type' => 'project'
```

Or multiple post types:

```php
'post_types' => [
    'project',
    'case_study',
    'office',
]
```

Or all post types using a wildcard:

```php
'post_type' => '*'
```

---

## Configuration Options

| Key | Type | Default | Description |
|---|---|---|---|
| `label` | `string` | *(required)* | Field title displayed in the admin. |
| `type` | `string` | `'text'` | Registered field type. |
| `description`| `string` | `''` | Helper text displayed below input. |
| `default` | `mixed` | `null` | Default fallback value. |
| `validation` | `array` | `[]` | Validation rules (e.g. `['required' => true]`). |
| `conditions` | `array` | `[]` | Conditional visibility rules. |
| `show_in_rest`| `bool\|array`| `false` | Expose field in the WordPress REST API. |
| `attributes` | `array` | `[]` | Additional HTML input attributes. |
| `assets` | `array` | `[]` | Field-specific styles and scripts. |

---

## Public Query API

### `Field::get($id, $postId = null, $default = null)`
Retrieves the saved field value for a post. If `$postId` is omitted, defaults to the current global post ID.

```php
$location = Field::get('location');
```

### `Field::set($id, $postId, $value)`
Saves a value for a post, running sanitization automatically.

```php
Field::set('location', 42, 'San Francisco');
```

### `Field::delete($id, $postId)`
Deletes the post meta entry.

```php
Field::delete('location', 42);
```

### `Field::url($id, $postId = null, $size = 'full')`
Convenience helper for media fields. Returns the attachment URL directly.

```php
$logoUrl = Field::url('icon', null, 'medium');
```

### `Field::attachment($id, $postId = null)`
Returns full attachment details (URL, title, caption, description, alt text, dimensions).

```php
$iconData = Field::attachment('icon');
```

### `Field::images($id, $postId = null, $size = 'full')`
Returns an array of image URLs for gallery fields.

```php
$photos = Field::images('gallery', get_the_ID(), 'large');
```
