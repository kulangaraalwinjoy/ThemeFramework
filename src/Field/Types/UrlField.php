<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Field\Types;

use KulangaraAlwinJoy\ThemeFramework\Security\Sanitizer;

/**
 * URL input field type.
 */
class UrlField extends BaseFieldType
{
    /**
     * {@inheritdoc}
     */
    public function render(string $name, mixed $value, array $field): void
    {
        $id = esc_attr((string) ($field['id'] ?? $name));
        $attrs = $this->buildAttributes($field, ['class' => 'regular-text code']);
        $strValue = esc_url((string) ($value ?? $field['default'] ?? ''));

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        printf(
            '<input type="url" id="%s" name="%s" value="%s"%s />',
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
        return Sanitizer::url($value);
    }
}
