# Custom Field Types

The framework architecture allows third-party developers and themes to add custom field types without modifying the framework core.

---

## 1. Implement `FieldTypeInterface`

Extend `BaseFieldType` to get validation and HTML attribute handling for free:

```php
namespace MyTheme\Fields;

use KulangaraAlwinJoy\ThemeFramework\Field\Types\BaseFieldType;

class ColorPickerField extends BaseFieldType
{
    /**
     * Render the field input HTML.
     */
    public function render(string $name, mixed $value, array $field): void
    {
        $id = esc_attr($field['id'] ?? $name);
        $val = esc_attr((string) ($value ?? $field['default'] ?? '#ffffff'));

        printf(
            '<input type="text" id="%s" name="%s" value="%s" class="my-color-field" data-default-color="%s" />',
            $id,
            esc_attr($name),
            $val,
            $val
        );
    }

    /**
     * Sanitize user input.
     */
    public function sanitize(mixed $value, array $field): mixed
    {
        $val = sanitize_hex_color((string) $value);
        return $val ?: '#ffffff';
    }

    /**
     * Enqueue field-specific scripts and styles.
     */
    public function enqueueAssets(array $field): void
    {
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
    }
}
```

---

## 2. Register with `FieldType` Registry

In your theme's bootstrap or `functions.php`:

```php
use KulangaraAlwinJoy\ThemeFramework\Field\FieldType;
use MyTheme\Fields\ColorPickerField;

FieldType::register('color', ColorPickerField::class);
```

You can also pass a factory callback:

```php
FieldType::register('color', fn () => new ColorPickerField());
```

---

## 3. Use in Any Context

Once registered, your custom field type immediately works across:
* **Post Fields**: `Field::add('accent_color', ['type' => 'color', 'post_type' => 'project'])`
* **Menu Fields**: `MenuField::add('badge_color', ['type' => 'color'])`
* **Theme Options**: `Option::add('brand_color', ['type' => 'color', 'page' => 'theme_settings'])`
* **Field Groups**: Nested inside `FieldGroup::add(...)`
