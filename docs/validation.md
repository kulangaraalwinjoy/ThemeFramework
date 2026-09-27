# Validation

The framework enforces strict server-side validation before saving any post field, menu field, or theme option.

---

## Defining Validation Rules

Add a `validation` array to your field configuration:

```php
use YourVendor\ThemeFramework\Field;

Field::add('user_age', [
    'label'      => 'Age',
    'type'       => 'number',
    'post_type'  => 'member',
    'validation' => [
        'required' => true,
        'min'      => 18,
        'max'      => 120,
    ],
]);
```

---

## Built-in Validation Rules

| Rule | Parameter Example | Description |
|---|---|---|
| `required` | `true` | Value cannot be null, empty string, or empty array. |
| `min` | `10` | Numeric value must be greater than or equal to minimum. |
| `max` | `100` | Numeric value must be less than or equal to maximum. |
| `min_length` | `3` | String character length must be at least specified value. |
| `max_length` | `50` | String character length must not exceed specified value. |
| `email` | `true` | Validates standard email address format. |
| `url` | `true` | Validates standard web URL format. |
| `in` | `['a', 'b', 'c']` | Value must be in the specified list of allowed values. |
| `regex` | `'/^[A-Z0-9]+$/'` | Value must match the given regular expression pattern. |

---

## Custom Validator Callback

You can specify an inline validation callback on any field:

```php
Field::add('postal_code', [
    'label'             => 'Postal Code',
    'type'              => 'text',
    'validate_callback' => function ($value, $field) {
        return preg_match('/^[0-9]{5}(-[0-9]{4})?$/', (string) $value) === 1;
    },
]);
```

---

## Extending the Global Validator

You can register custom validation rules available across the entire site:

```php
use YourVendor\ThemeFramework\Support\Validator;

Validator::extend('even_number', function ($value, $parameter, $field) {
    return is_numeric($value) && ((int) $value % 2 === 0);
});

// Use in any field:
Field::add('tickets', [
    'label'      => 'Tickets (Pairs)',
    'type'       => 'number',
    'validation' => [
        'even_number' => true,
    ],
]);
```
