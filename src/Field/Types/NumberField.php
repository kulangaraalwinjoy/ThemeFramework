<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Field\Types;

use KulangaraAlwinJoy\ThemeFramework\Security\Sanitizer;

/**
 * Numeric input field type.
 */
class NumberField extends BaseFieldType
{
    /**
     * {@inheritdoc}
     */
    public function render(string $name, mixed $value, array $field): void
    {
        $id = esc_attr((string) ($field['id'] ?? $name));
        $defaults = ['class' => 'small-text'];

        if (isset($field['min'])) {
            $defaults['min'] = (string) $field['min'];
        }
        if (isset($field['max'])) {
            $defaults['max'] = (string) $field['max'];
        }
        if (isset($field['step'])) {
            $defaults['step'] = (string) $field['step'];
        }

        $attrs = $this->buildAttributes($field, $defaults);
        $val = $value ?? $field['default'] ?? '';
        $strValue = $val !== '' && $val !== null ? esc_attr((string) $val) : '';

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        printf(
            '<input type="number" id="%s" name="%s" value="%s"%s />',
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
        $step = (string) ($field['step'] ?? '1');
        $allowFloat = str_contains($step, '.') || $step === 'any';
        return Sanitizer::number($value, $allowFloat);
    }
}
