<?php

declare(strict_types=1);

namespace YourVendor\ThemeFramework\Field\Types;

use YourVendor\ThemeFramework\Security\Sanitizer;

/**
 * Select dropdown field type.
 */
class SelectField extends BaseFieldType
{
    /**
     * {@inheritdoc}
     */
    public function render(string $name, mixed $value, array $field): void
    {
        $id = esc_attr((string) ($field['id'] ?? $name));
        $multiple = !empty($field['multiple']);
        $attrs = $this->buildAttributes($field);
        $inputName = $multiple ? esc_attr($name) . '[]' : esc_attr($name);
        $choices = (array) ($field['choices'] ?? $field['options'] ?? []);

        $selectedValues = $multiple ? (array) $value : [$value];
        $selectedValues = array_map('strval', $selectedValues);

        $html = sprintf(
            '<select id="%s" name="%s"%s%s>',
            $id,
            $inputName,
            $multiple ? ' multiple="multiple"' : '',
            $attrs
        );

        if (!empty($field['placeholder']) && !$multiple) {
            $html .= sprintf(
                '<option value="">%s</option>',
                esc_html((string) $field['placeholder'])
            );
        }

        foreach ($choices as $optVal => $optLabel) {
            $strOptVal = (string) $optVal;
            $isSelected = in_array($strOptVal, $selectedValues, true);
            $html .= sprintf(
                '<option value="%s"%s>%s</option>',
                esc_attr($strOptVal),
                $isSelected ? ' selected="selected"' : '',
                esc_html((string) $optLabel)
            );
        }

        $html .= '</select>';

        echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    /**
     * {@inheritdoc}
     */
    public function sanitize(mixed $value, array $field): mixed
    {
        $choices = (array) ($field['choices'] ?? $field['options'] ?? []);
        $multiple = !empty($field['multiple']);
        return Sanitizer::select($value, $choices, $multiple);
    }
}
