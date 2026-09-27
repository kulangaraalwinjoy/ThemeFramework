<?php

declare(strict_types=1);

namespace YourVendor\ThemeFramework\Field\Types;

use YourVendor\ThemeFramework\Security\Sanitizer;

/**
 * Email input field type.
 */
class EmailField extends BaseFieldType
{
    /**
     * {@inheritdoc}
     */
    public function render(string $name, mixed $value, array $field): void
    {
        $id = esc_attr((string) ($field['id'] ?? $name));
        $attrs = $this->buildAttributes($field, ['class' => 'regular-text']);
        $strValue = esc_attr((string) ($value ?? $field['default'] ?? ''));

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        printf(
            '<input type="email" id="%s" name="%s" value="%s"%s />',
            $id,
            esc_attr($name),
            $strValue,
            $attrs
        );
    }

    /**
     * {@inheritdoc}
     */
    public function sanitize(mixed $value, array $field): mixed
    {
        return Sanitizer::email($value);
    }
}
