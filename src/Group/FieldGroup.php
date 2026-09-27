<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Group;

use KulangaraAlwinJoy\ThemeFramework\Field\Field;
use KulangaraAlwinJoy\ThemeFramework\Field\FieldRegistry;
use KulangaraAlwinJoy\ThemeFramework\Field\FieldRenderer;
use KulangaraAlwinJoy\ThemeFramework\Security\Nonce;

/**
 * Public API Facade and Meta Box integration for Field Groups.
 */
class FieldGroup
{
    /**
     * Add a new field group.
     *
     * @param string $id
     * @param array<string, mixed> $config
     * @return void
     */
    public static function add(string $id, array $config): void
    {
        FieldGroupRegistry::add($id, $config);
    }

    /**
     * Hook callback for add_meta_boxes.
     *
     * @param string $postType Current screen post type.
     * @return void
     */
    public static function registerMetaBoxes(string $postType): void
    {
        // 1. Register explicit field groups
        $groups = FieldGroupRegistry::forPostType($postType);
        $groupedFieldIds = [];

        foreach ($groups as $groupId => $group) {
            $title = (string) ($group['title'] ?? 'Fields');
            $rawContext = (string) ($group['context'] ?? 'normal');
            $context = in_array($rawContext, ['normal', 'side', 'advanced'], true) ? $rawContext : 'normal';
            $rawPriority = (string) ($group['priority'] ?? 'default');
            $priority = in_array($rawPriority, ['core', 'default', 'high', 'low'], true) ? $rawPriority : 'default';
            $nestedFields = array_keys((array) ($group['fields'] ?? []));
            $groupedFieldIds = array_merge($groupedFieldIds, $nestedFields);

            add_meta_box(
                'theme_group_' . $groupId,
                $title,
                function ($post) use ($group) {
                    self::renderGroupMetaBox($post, $group);
                },
                $postType,
                $context,
                $priority
            );
        }

        // 2. Register a default meta box for standalone fields not inside an explicit group
        $allFields = FieldRegistry::forPostType($postType);
        $standaloneFields = [];
        foreach ($allFields as $fieldId => $field) {
            if (!in_array($fieldId, $groupedFieldIds, true) && empty($field['group'])) {
                $standaloneFields[$fieldId] = $field;
            }
        }

        if (!empty($standaloneFields)) {
            $defaultTitle = (string) apply_filters(
                'theme_framework_default_metabox_title',
                __('Additional Details', 'wp-theme-framework'),
                $postType
            );

            add_meta_box(
                'theme_default_fields_' . $postType,
                $defaultTitle,
                function ($post) use ($standaloneFields) {
                    self::renderStandaloneMetaBox($post, $standaloneFields);
                },
                $postType,
                'normal',
                'default'
            );
        }
    }

    /**
     * Render an explicit group meta box.
     *
     * @param mixed $post
     * @param array<string, mixed> $group
     * @return void
     */
    protected static function renderGroupMetaBox(mixed $post, array $group): void
    {
        $postId = is_object($post) && isset($post->ID) ? (int) $post->ID : (int) $post;
        $fields = (array) ($group['fields'] ?? []);
        $renderer = new FieldRenderer();

        // Render nonce once per post screen
        Nonce::field('post_fields_' . $postId, '_theme_framework_nonce', false, true);

        echo '<div class="theme-framework-group-fields">';
        foreach ($fields as $fieldId => $fieldConfig) {
            $fullField = FieldRegistry::get((string) $fieldId) ?? array_merge(['id' => $fieldId], $fieldConfig);
            $value = Field::get((string) $fieldId, $postId, $fullField['default'] ?? null);
            $renderer->renderRow($fullField, $value, (string) $fieldId, $postId);
        }
        echo '</div>';
    }

    /**
     * Render standalone fields inside the default meta box.
     *
     * @param mixed $post
     * @param array<string, array<string, mixed>> $fields
     * @return void
     */
    protected static function renderStandaloneMetaBox(mixed $post, array $fields): void
    {
        $postId = is_object($post) && isset($post->ID) ? (int) $post->ID : (int) $post;
        $renderer = new FieldRenderer();

        // Render nonce
        Nonce::field('post_fields_' . $postId, '_theme_framework_nonce', false, true);

        echo '<div class="theme-framework-standalone-fields">';
        foreach ($fields as $fieldId => $field) {
            $value = Field::get($fieldId, $postId, $field['default'] ?? null);
            $renderer->renderRow($field, $value, $fieldId, $postId);
        }
        echo '</div>';
    }
}
