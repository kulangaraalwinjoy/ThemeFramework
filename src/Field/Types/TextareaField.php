<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Field\Types;

use KulangaraAlwinJoy\ThemeFramework\Security\Sanitizer;

/**
 * Multi-line textarea field type.
 */
class TextareaField extends BaseFieldType
{
    /**
     * {@inheritdoc}
     */
    public function render(string $name, mixed $value, array $field): void
    {
        $id = esc_attr((string) ($field['id'] ?? $name));
        $rows = (int) ($field['rows'] ?? 6);
        $attrs = $this->buildAttributes($field, ['class' => 'large-text']);
        $strValue = esc_textarea((string) ($value ?? $field['default'] ?? ''));

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        printf(
            '<textarea id="%s" name="%s" rows="%d"%s>%s</textarea>',
            $id,
            esc_attr($name),
            $rows,
            $attrs,
            $strValue
        );
    }

    /**
     * {@inheritdoc}
     */
    public function sanitize(mixed $value, array $field): mixed
    {
        $mode = $field['sanitize_mode'] ?? $field['format'] ?? null;
        if ($mode === 'css') {
            return Sanitizer::css($value);
        }
        if ($mode === 'js' || $mode === 'javascript') {
            return Sanitizer::javascript($value);
        }

        return Sanitizer::textarea($value);
    }
}
