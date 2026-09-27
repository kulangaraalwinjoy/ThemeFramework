<?php

declare(strict_types=1);

namespace YourVendor\ThemeFramework\Field\Types;

use YourVendor\ThemeFramework\Field\Contracts\FieldTypeInterface;
use YourVendor\ThemeFramework\Support\Validator;

/**
 * Base abstract class providing common utility methods for field types.
 */
abstract class BaseFieldType implements FieldTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function validate(mixed $value, array $field): bool
    {
        $rules = (array) ($field['validation'] ?? []);
        return Validator::check($value, $rules, $field);
    }

    /**
     * {@inheritdoc}
     */
    public function enqueueAssets(array $field): void
    {
        // Default: no specialized assets needed by base field
    }

    /**
     * Build common HTML attribute string from field attributes.
     *
     * @param array<string, mixed> $field
     * @param array<string, string> $defaults
     * @return string
     */
    protected function buildAttributes(array $field, array $defaults = []): string
    {
        $attrs = array_merge($defaults, (array) ($field['attributes'] ?? []));

        if (!empty($field['placeholder'])) {
            $attrs['placeholder'] = (string) $field['placeholder'];
        }

        if (!empty($field['validation']['required']) || !empty($field['required'])) {
            $attrs['required'] = 'required';
        }

        if (!empty($field['readonly'])) {
            $attrs['readonly'] = 'readonly';
        }

        if (!empty($field['disabled'])) {
            $attrs['disabled'] = 'disabled';
        }

        $html = '';
        foreach ($attrs as $key => $val) {
            if ($val === true || $val === 'required' || $val === 'readonly' || $val === 'disabled') {
                $html .= ' ' . esc_attr($key);
            } else {
                $html .= ' ' . esc_attr($key) . '="' . esc_attr((string) $val) . '"';
            }
        }

        return $html;
    }
}
