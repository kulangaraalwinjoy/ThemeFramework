# Built-in Field Types

The framework includes 9 built-in field types plus a custom callable type.

---

## 1. `text`
Standard single-line text input.

```php
Field::add('subtitle', [
    'label' => 'Subtitle',
    'type'  => 'text',
]);
```

---

## 2. `textarea`
Multi-line text area.

```php
Field::add('bio', [
    'label' => 'Biography',
    'type'  => 'textarea',
    'rows'  => 8,
]);
```

Supports `sanitize_mode => 'css'` and `sanitize_mode => 'js'` for specialized code fields.

---

## 3. `number`
Numeric input with bounds and step control.

```php
Field::add('price', [
    'label' => 'Price (USD)',
    'type'  => 'number',
    'min'   => 0,
    'max'   => 10000,
    'step'  => '0.01',
]);
```

---

## 4. `url`
URL input sanitized with `esc_url_raw()`.

```php
Field::add('website', [
    'label' => 'External Website',
    'type'  => 'url',
]);
```

---

## 5. `email`
Email input validated and sanitized with `sanitize_email()`.

```php
Field::add('contact_email', [
    'label' => 'Contact Email',
    'type'  => 'email',
]);
```

---

## 6. `checkbox`
Boolean checkbox input. Stores `1` or `0`.

```php
Field::add('featured', [
    'label'          => 'Featured Item',
    'type'           => 'checkbox',
    'checkbox_label' => 'Display this item prominently in featured sections',
]);
```

---

## 7. `select`
Single or multi-select dropdown.

```php
Field::add('layout', [
    'label'   => 'Page Layout',
    'type'    => 'select',
    'choices' => [
        'default' => 'Default (Right Sidebar)',
        'left'    => 'Left Sidebar',
        'full'    => 'Full Width',
    ],
    'default' => 'default',
]);

// Multi-select:
Field::add('categories', [
    'label'    => 'Categories',
    'type'     => 'select',
    'multiple' => true,
    'choices'  => [ ... ],
]);
```

---

## 8. `media`
Single attachment picker integrating with the native WordPress Media Library. Stores attachment ID.

```php
Field::add('hero_image', [
    'label'         => 'Hero Image',
    'type'          => 'media',
    'allowed_types' => ['image/png', 'image/jpeg', 'image/webp'],
]);
```

---

## 9. `gallery`
Multi-image gallery with drag-and-drop sortable thumbnail reordering. Stores array of attachment IDs.

```php
Field::add('project_gallery', [
    'label' => 'Project Gallery',
    'type'  => 'gallery',
]);
```

---

## 10. `custom`
Escape hatch allowing custom render and sanitize callbacks.

```php
Field::add('special_code', [
    'label'  => 'Special Code',
    'type'   => 'custom',
    'render' => function ($field, $value, $objectId) {
        echo '<input type="text" name="' . esc_attr($field['id']) . '" value="' . esc_attr((string) $value) . '" />';
    },
    'sanitize' => function ($value, $field) {
        return strtoupper(trim((string) $value));
    },
]);
```
