<?php

declare(strict_types=1);

namespace YourVendor\ThemeFramework\Field;

use YourVendor\ThemeFramework\Security\Capability;
use YourVendor\ThemeFramework\Security\Nonce;
use YourVendor\ThemeFramework\Storage\StorageInterface;
use YourVendor\ThemeFramework\Support\Conditions;

/**
 * Handles security checks, validation, sanitization, and persistence of field values.
 */
class FieldSaver
{
    /**
     * Save a set of fields for a target object.
     *
     * @param int $objectId
     * @param array<string, array<string, mixed>> $fields
     * @param StorageInterface $storage
     * @param array<string, mixed> $data Request data (e.g. $_POST).
     * @param string $nonceAction
     * @param string $nonceField
     * @param string|null $capability
     * @return bool
     */
    public function save(
        int $objectId,
        array $fields,
        StorageInterface $storage,
        array $data,
        string $nonceAction,
        string $nonceField = '_theme_framework_nonce',
        ?string $capability = null
    ): bool {
        if ($objectId <= 0 || empty($fields)) {
            return false;
        }

        // Avoid saving during WordPress autosave or revisions
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }

        if (function_exists('wp_is_post_revision') && wp_is_post_revision($objectId)) {
            return false;
        }

        if (function_exists('wp_is_post_autosave') && wp_is_post_autosave($objectId)) {
            return false;
        }

        // 1. Verify Nonce
        $nonce = isset($data[$nonceField]) ? (string) $data[$nonceField] : null;
        if (!Nonce::verify($nonce, $nonceAction)) {
            return false;
        }

        // 2. Verify Capability
        if ($capability !== null) {
            if (!Capability::can($capability, $objectId)) {
                return false;
            }
        } elseif (!Capability::canEditPost($objectId)) {
            return false;
        }

        $validator = new FieldValidator();

        // 3. Process and persist each field
        foreach ($fields as $fieldId => $field) {
            $inputName = (string) ($field['id'] ?? $fieldId);

            // If field not present in request payload, check if it was a checkbox
            $type = (string) ($field['type'] ?? 'text');
            if (!array_key_exists($inputName, $data)) {
                if ($type === 'checkbox') {
                    $rawValue = 0;
                } else {
                    continue;
                }
            } else {
                $rawValue = $data[$inputName];
            }

            // Check conditional logic against submitted data or stored data
            $conditions = (array) ($field['conditions'] ?? []);
            if (!empty($conditions)) {
                $matches = Conditions::evaluate($conditions, function ($condField) use ($data, $storage, $objectId) {
                    if (array_key_exists($condField, $data)) {
                        return $data[$condField];
                    }
                    return $storage->get($condField, $objectId);
                });

                if (!$matches) {
                    // When conditions are not met, the field was hidden in UI.
                    // Keep or clear based on config (default: do not overwrite)
                    continue;
                }
            }

            // 4. Validate
            if (!$validator->validate($rawValue, $field)) {
                continue;
            }

            // 5. Sanitize
            if (!empty($field['sanitize']) && is_callable($field['sanitize'])) {
                $sanitized = call_user_func($field['sanitize'], $rawValue, $field);
            } else {
                $handler = FieldType::get($type);
                $sanitized = $handler->sanitize($rawValue, $field);
            }

            // 6. Persist or delete when empty
            $isEmpty = $sanitized === null || $sanitized === '' || $sanitized === [];
            if ($isEmpty && $type !== 'checkbox') {
                $storage->delete($fieldId, $objectId);
                do_action('theme_framework_field_deleted', $field, $objectId);
            } else {
                $storage->set($fieldId, $sanitized, $objectId);
                do_action('theme_framework_field_saved', $field, $sanitized, $objectId);
            }
        }

        return true;
    }
}
