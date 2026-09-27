<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Field\Contracts;

/**
 * Common contract for all field types.
 */
interface FieldTypeInterface
{
    /**
     * Render the field input HTML.
     *
     * @param string $name Form input name attribute.
     * @param mixed $value Current field value.
     * @param array<string, mixed> $field Complete field definition array.
     * @return void
     */
    public function render(
        string $name,
        mixed $value,
        array $field
    ): void;

    /**
     * Sanitize the submitted value.
     *
     * @param mixed $value
     * @param array<string, mixed> $field
     * @return mixed
     */
    public function sanitize(
        mixed $value,
        array $field
    ): mixed;

    /**
     * Validate the field value.
     *
     * @param mixed $value
     * @param array<string, mixed> $field
     * @return bool
     */
    public function validate(
        mixed $value,
        array $field
    ): bool;

    /**
     * Enqueue field-specific scripts and styles.
     *
     * @param array<string, mixed> $field
     * @return void
     */
    public function enqueueAssets(
        array $field
    ): void;
}
