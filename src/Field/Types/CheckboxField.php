<?php

declare(strict_types=1);

namespace YourVendor\ThemeFramework\Field\Types;

use YourVendor\ThemeFramework\Security\Sanitizer;

/**
 * Checkbox field type.
 */
class CheckboxField extends BaseFieldType
{
    /**
     * {@inheritdoc}
     */
    public function render(string $name, mixed $value, array $field): void
    {
        $id = esc_attr((string) ($field['id'] ?? $name));
        $attrs = $this->buildAttributes($field);
        $isChecked = !empty($value) && $value !== '0' && $value !== false;
        $checkboxLabel = !empty($field['checkbox_label']) ? (string) $field['checkbox_label'] : '';

        // Hidden input ensures a value of 0 is sent when unchecked
        printf(
            '<input type="hidden" name="%s" value="0" />',
            esc_attr($name)
        );

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        printf(
            '<label for="%s"><input type="checkbox" id="%s" name="%s" value="1"%s%s /> %s</label>',
            $id,
            $id,
            esc_attr($name),
            $isChecked ? ' checked="checked"' : '',
            $attrs,
            esc_html($checkboxLabel)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function sanitize(mixed $value, array $field): mixed
    {
        return Sanitizer::checkbox($value);
    }
}
