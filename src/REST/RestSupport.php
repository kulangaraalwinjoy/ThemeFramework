<?php

declare(strict_types=1);

namespace YourVendor\ThemeFramework\REST;

use WP_Error;
use YourVendor\ThemeFramework\Field\Field;
use YourVendor\ThemeFramework\Field\FieldRegistry;
use YourVendor\ThemeFramework\Field\FieldType;
use YourVendor\ThemeFramework\Field\FieldValidator;
use YourVendor\ThemeFramework\Security\Capability;

/**
 * Handles WordPress REST API registration and exposure for fields.
 */
class RestSupport
{
    /**
     * Register meta fields for the REST API using register_post_meta.
     *
     * @return void
     */
    public static function registerRestFields(): void
    {
        $fields = FieldRegistry::all();
        $storage = Field::getStorage();

        foreach ($fields as $fieldId => $field) {
            if (empty($field['show_in_rest'])) {
                continue;
            }

            $metaKey = $storage->getMetaKey($fieldId);
            $type = (string) ($field['type'] ?? 'text');
            $postTypes = (array) ($field['post_types'] ?? []);

            // If empty or wildcard '*', use empty string for all post types
            if (empty($postTypes) || in_array('*', $postTypes, true)) {
                $postTypes = [''];
            }

            $schema = self::buildRestSchema($field, $type);
            $isSingle = $type !== 'gallery';

            $args = [
                'type' => self::mapRestType($type),
                'description' => (string) ($field['description'] ?? $field['label'] ?? ''),
                'single' => $isSingle,
                'show_in_rest' => is_array($field['show_in_rest']) ? $field['show_in_rest'] : [
                    'schema' => $schema,
                ],
                'auth_callback' => function ($allowed, $metaKey, $postId) {
                    return Capability::canEditPost((int) $postId);
                },
                'sanitize_callback' => function ($value) use ($field, $type) {
                    $validator = new FieldValidator();
                    if (!$validator->validate($value, $field)) {
                        return new WP_Error(
                            'rest_invalid_param',
                            sprintf(
                                __('Invalid field value for "%s".', 'wp-theme-framework'),
                                $field['label'] ?? $field['id']
                            ),
                            ['status' => 400]
                        );
                    }

                    if (!empty($field['sanitize']) && is_callable($field['sanitize'])) {
                        return call_user_func($field['sanitize'], $value, $field);
                    }

                    $handler = FieldType::get($type);
                    return $handler->sanitize($value, $field);
                },
            ];

            foreach ($postTypes as $pt) {
                register_post_meta($pt, $metaKey, $args);
            }
        }
    }

    /**
     * Map field type to REST primitive type.
     *
     * @param string $type
     * @return string
     */
    public static function mapRestType(string $type): string
    {
        return match ($type) {
            'number' => 'number',
            'checkbox' => 'boolean',
            'media' => 'integer',
            'gallery' => 'array',
            default => 'string',
        };
    }

    /**
     * Build JSON Schema for REST API response.
     *
     * @param array<string, mixed> $field
     * @param string $type
     * @return array<string, mixed>
     */
    public static function buildRestSchema(array $field, string $type): array
    {
        $schema = [
            'type' => self::mapRestType($type),
            'description' => (string) ($field['description'] ?? $field['label'] ?? ''),
        ];

        if ($type === 'gallery') {
            $schema['items'] = [
                'type' => 'integer',
            ];
        }

        if (isset($field['default'])) {
            $schema['default'] = $field['default'];
        }

        return $schema;
    }
}
