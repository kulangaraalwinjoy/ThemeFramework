<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Field\Types;

/**
 * Custom field type acting as an escape hatch for custom callback rendering.
 */
class CustomField extends BaseFieldType
{
    /**
     * {@inheritdoc}
     */
    public function render(string $name, mixed $value, array $field): void
    {
        $callback = $field['render'] ?? null;
        if (is_callable($callback)) {
            $objectId = $field['_current_object_id'] ?? null;
            call_user_func($callback, $field, $value, $objectId);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sanitize(mixed $value, array $field): mixed
    {
        $callback = $field['sanitize'] ?? null;
        if (is_callable($callback)) {
            return call_user_func($callback, $value, $field);
        }

        return $value;
    }
}
