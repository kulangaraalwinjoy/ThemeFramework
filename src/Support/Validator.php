<?php

declare(strict_types=1);

namespace YourVendor\ThemeFramework\Support;

/**
 * Server-side data validator with extensible rules.
 */
class Validator
{
    /**
     * Custom rule registry.
     *
     * @var array<string, callable>
     */
    protected static array $customRules = [];

    /**
     * Last validation error messages.
     *
     * @var array<string>
     */
    protected array $errors = [];

    /**
     * Extend with a custom validation rule.
     *
     * @param string $rule
     * @param callable $callback function(mixed $value, mixed $parameter, array $field): bool
     * @return void
     */
    public static function extend(string $rule, callable $callback): void
    {
        self::$customRules[$rule] = $callback;
    }

    /**
     * Reset custom rules.
     *
     * @return void
     */
    public static function resetCustomRules(): void
    {
        self::$customRules = [];
    }

    /**
     * Validate a value against a set of rules.
     *
     * @param mixed $value
     * @param array<string|int, mixed> $rules
     * @param array<string, mixed> $field
     * @return bool
     */
    public function validate(mixed $value, array $rules, array $field = []): bool
    {
        $this->errors = [];
        $fieldName = (string) ($field['label'] ?? $field['id'] ?? 'Field');

        foreach ($rules as $rule => $parameter) {
            if (is_int($rule)) {
                $rule = (string) $parameter;
                $parameter = true;
            }

            if (!$this->validateRule((string) $rule, $value, $parameter, $field)) {
                $this->addError((string) $rule, $fieldName, $parameter);
            }
        }

        return empty($this->errors);
    }

    /**
     * Static helper for quick validation.
     *
     * @param mixed $value
     * @param array<string|int, mixed> $rules
     * @param array<string, mixed> $field
     * @return bool
     */
    public static function check(mixed $value, array $rules, array $field = []): bool
    {
        $instance = new self();
        return $instance->validate($value, $rules, $field);
    }

    /**
     * Validate a single rule.
     *
     * @param string $rule
     * @param mixed $value
     * @param mixed $parameter
     * @param array<string, mixed> $field
     * @return bool
     */
    protected function validateRule(string $rule, mixed $value, mixed $parameter, array $field): bool
    {
        // Check custom rules first
        if (isset(self::$customRules[$rule])) {
            return (bool) call_user_func(self::$customRules[$rule], $value, $parameter, $field);
        }

        // If field is not required and empty, skip remaining validations
        $isEmpty = $value === null || $value === '' || $value === [];
        if ($rule !== 'required' && $isEmpty) {
            return true;
        }

        return match ($rule) {
            'required' => $this->validateRequired($value, $parameter),
            'min' => $this->validateMin($value, $parameter),
            'max' => $this->validateMax($value, $parameter),
            'min_length' => $this->validateMinLength($value, $parameter),
            'max_length' => $this->validateMaxLength($value, $parameter),
            'email' => $this->validateEmail($value),
            'url' => $this->validateUrl($value),
            'in' => in_array($value, (array) $parameter, true),
            'regex' => is_string($value) && is_string($parameter) && (bool) preg_match($parameter, $value),
            default => true,
        };
    }

    /**
     * Validate required.
     *
     * @param mixed $value
     * @param mixed $parameter
     * @return bool
     */
    protected function validateRequired(mixed $value, mixed $parameter): bool
    {
        if (!$parameter) {
            return true;
        }

        if ($value === null) {
            return false;
        }

        if (is_string($value) && trim($value) === '') {
            return false;
        }

        if (is_countable($value) && count($value) === 0) {
            return false;
        }

        return true;
    }

    /**
     * Validate minimum numeric value.
     *
     * @param mixed $value
     * @param mixed $parameter
     * @return bool
     */
    protected function validateMin(mixed $value, mixed $parameter): bool
    {
        return is_numeric($value) && is_numeric($parameter) && (float) $value >= (float) $parameter;
    }

    /**
     * Validate maximum numeric value.
     *
     * @param mixed $value
     * @param mixed $parameter
     * @return bool
     */
    protected function validateMax(mixed $value, mixed $parameter): bool
    {
        return is_numeric($value) && is_numeric($parameter) && (float) $value <= (float) $parameter;
    }

    /**
     * Validate minimum string length.
     *
     * @param mixed $value
     * @param mixed $parameter
     * @return bool
     */
    protected function validateMinLength(mixed $value, mixed $parameter): bool
    {
        return is_string($value) && is_numeric($parameter) && mb_strlen($value, 'UTF-8') >= (int) $parameter;
    }

    /**
     * Validate maximum string length.
     *
     * @param mixed $value
     * @param mixed $parameter
     * @return bool
     */
    protected function validateMaxLength(mixed $value, mixed $parameter): bool
    {
        return is_string($value) && is_numeric($parameter) && mb_strlen($value, 'UTF-8') <= (int) $parameter;
    }

    /**
     * Validate email format.
     *
     * @param mixed $value
     * @return bool
     */
    protected function validateEmail(mixed $value): bool
    {
        return is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate URL format.
     *
     * @param mixed $value
     * @return bool
     */
    protected function validateUrl(mixed $value): bool
    {
        return is_string($value) && filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Add an error message.
     *
     * @param string $rule
     * @param string $field
     * @param mixed $parameter
     * @return void
     */
    protected function addError(string $rule, string $field, mixed $parameter): void
    {
        $message = match ($rule) {
            'required' => sprintf('%s is required.', $field),
            'min' => sprintf('%s must be at least %s.', $field, (string) $parameter),
            'max' => sprintf('%s cannot exceed %s.', $field, (string) $parameter),
            'min_length' => sprintf('%s must be at least %s characters.', $field, (string) $parameter),
            'max_length' => sprintf('%s cannot exceed %s characters.', $field, (string) $parameter),
            'email' => sprintf('%s must be a valid email address.', $field),
            'url' => sprintf('%s must be a valid URL.', $field),
            'in' => sprintf('%s contains an invalid selection.', $field),
            'regex' => sprintf('%s format is invalid.', $field),
            default => sprintf('%s is invalid.', $field),
        };

        $this->errors[] = $message;
    }

    /**
     * Get all accumulated validation errors.
     *
     * @return array<string>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
