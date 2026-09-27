# REST API Integration

The framework provides native WordPress REST API integration using official WordPress APIs (`register_post_meta()`).

---

## Enabling REST Support on Fields

To expose a post field in the WordPress REST API and the Block Editor, set `show_in_rest => true`:

```php
use YourVendor\ThemeFramework\Field;

Field::add('location', [
    'label'        => 'Location',
    'type'         => 'text',
    'post_type'    => 'project',
    'show_in_rest' => true,
]);
```

---

## What the Framework Handles Automatically

When `show_in_rest` is enabled, the framework:

1. **Registers Post Meta**: Calls `register_post_meta()` during the `init` hook.
2. **Schema Type Mapping**: Automatically maps field types to JSON Schema primitive types:
   * `text`, `textarea`, `url`, `email` &rarr; `string`
   * `number` &rarr; `number`
   * `checkbox` &rarr; `boolean`
   * `media` &rarr; `integer`
   * `gallery` &rarr; `array` of integers
3. **Authorization**: Applies capability checks (`edit_post` on the target post ID) before allowing updates through REST endpoints.
4. **Validation**: Runs the exact same `FieldValidator` rules as admin saves. Invalid payloads return a `400 Bad Request` with a structured `WP_Error`.
5. **Sanitization**: Applies the field type's `sanitize()` method before persisting.

---

## Custom REST Schema Configuration

You can customize the schema definition by passing an array to `show_in_rest`:

```php
Field::add('priority', [
    'label'        => 'Priority',
    'type'         => 'number',
    'post_type'    => 'project',
    'show_in_rest' => [
        'schema' => [
            'type'        => 'integer',
            'minimum'     => 1,
            'maximum'     => 5,
            'description' => 'Project priority from 1 to 5',
        ],
    ],
]);
```

---

## Consuming REST Fields

When querying the post type via the REST API (e.g. `GET /wp-json/wp/v2/projects/<id>`), registered fields appear in the `meta` object:

```json
{
  "id": 101,
  "title": { "rendered": "Flagship Project" },
  "meta": {
    "_theme_location": "San Francisco, CA",
    "_theme_icon": 501,
    "_theme_gallery": [601, 602]
  }
}
```
