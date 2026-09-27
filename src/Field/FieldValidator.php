<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Field;

use KulangaraAlwinJoy\ThemeFramework\Support\Validator;

/**
 * Validates field values using FieldType logic and validation rules.
 */
class FieldValidator
{
    /**
     * Last errors recorded.
     *
     * @var array<string>
     */
    protected array $errors = [];

    /**
     * Validate a field value.
     *
     * @param mixed $value
     * @param array<string, mixed> $field
     * @return bool
     */
    public function validate(mixed $value, array $field): bool
    {
        $this->errors = [];
        $type = (string) ($field['type'] ?? 'text');

        // FieldType-specific validation
        if (FieldType::has($type)) {
            $handler = FieldType::get($type);
            if (!$handler->validate($value, $field)) {
                $this->errors[] = sprintf(
                    '%s failed validation.',
                    (string) ($field['label'] ?? $field['id'] ?? 'Field')
                );
                return false;
            }
        }

        // General validation rules
        $rules = (array) ($field['validation'] ?? []);
        if (!empty($rules)) {
            $validator = new Validator();
            if (!$validator->validate($value, $rules, $field)) {
                $this->errors = array_merge($this->errors, $validator->getErrors());
                return false;
            }
        }

        // Custom validation callback
        if (!empty($field['validate_callback']) && is_callable($field['validate_callback'])) {
            $res = call_user_func($field['validate_callback'], $value, $field);
            if ($res === false) {
                $this->errors[] = sprintf(
                    '%s failed custom validation check.',
                    (string) ($field['label'] ?? $field['id'] ?? 'Field')
                );
                return false;
            }
        }

        return true;
    }

    /**
     * Get recorded error messages.
     *
     * @return array<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
