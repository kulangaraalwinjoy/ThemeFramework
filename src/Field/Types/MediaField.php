<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Field\Types;

use KulangaraAlwinJoy\ThemeFramework\Assets\AssetManager;
use KulangaraAlwinJoy\ThemeFramework\Security\Sanitizer;

/**
 * Media single-attachment field type integrating with the WordPress Media Library.
 */
class MediaField extends BaseFieldType
{
    /**
     * Resolve allowed MIME types from either allowed_types or library alias.
     *
     * @param array<string, mixed> $field
     * @return array<string>
     */
    public static function resolveAllowedTypes(array $field): array
    {
        $types = $field['allowed_types'] ?? $field['library'] ?? [];
        return array_values((array) $types);
    }

    /**
     * {@inheritdoc}
     */
    public function render(string $name, mixed $value, array $field): void
    {
        $id = esc_attr((string) ($field['id'] ?? $name));
        $attachmentId = absint($value);
        $allowedTypes = self::resolveAllowedTypes($field);
        $previewUrl = '';
        $fileName = '';
        $isImage = false;

        if ($attachmentId > 0) {
            $previewUrl = (string) wp_get_attachment_image_url($attachmentId, 'thumbnail');
            if ($previewUrl === '') {
                $previewUrl = (string) wp_get_attachment_url($attachmentId);
            }
            $post = get_post($attachmentId);
            if ($post) {
                $fileName = basename((string) get_attached_file($attachmentId));
                $mime = get_post_mime_type($attachmentId);
                $isImage = is_string($mime) && str_starts_with($mime, 'image/');
            }
        }

        $allowedJson = !empty($allowedTypes) ? esc_attr((string) wp_json_encode($allowedTypes)) : '';
        $hasAttachment = $attachmentId > 0 && $previewUrl !== '';

        ?>
        <div class="theme-framework-media-wrap" data-allowed-types="<?php echo $allowedJson; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" data-title="<?php echo esc_attr((string) ($field['modal_title'] ?? $field['label'] ?? 'Select Media')); ?>" data-button="<?php echo esc_attr((string) ($field['button_text'] ?? 'Use Media')); ?>">
            <input type="hidden" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" value="<?php echo $attachmentId > 0 ? (int) $attachmentId : ''; ?>" class="theme-framework-media-id" />

            <div class="theme-framework-media-preview" style="<?php echo !$hasAttachment ? 'display: none;' : ''; ?>">
                <?php if ($hasAttachment) : ?>
                    <?php if ($isImage) : ?>
                        <img src="<?php echo esc_url($previewUrl); ?>" alt="<?php echo esc_attr($fileName); ?>" style="max-width: 150px; max-height: 150px; height: auto; display: block; margin-bottom: 8px;" />
                    <?php else : ?>
                        <div class="theme-framework-media-file-info" style="margin-bottom: 8px;">
                            <span class="dashicons dashicons-media-default"></span>
                            <span class="theme-framework-media-filename"><?php echo esc_html($fileName); ?></span>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="theme-framework-media-actions">
                <button type="button" class="button theme-framework-media-select" style="<?php echo $hasAttachment ? 'display: none;' : ''; ?>">
                    <?php echo esc_html((string) ($field['select_button'] ?? 'Select Media')); ?>
                </button>
                <button type="button" class="button theme-framework-media-replace" style="<?php echo !$hasAttachment ? 'display: none;' : ''; ?>">
                    <?php echo esc_html((string) ($field['replace_button'] ?? 'Replace')); ?>
                </button>
                <button type="button" class="button button-link-delete theme-framework-media-remove" style="<?php echo !$hasAttachment ? 'display: none;' : ''; ?>">
                    <?php echo esc_html((string) ($field['remove_button'] ?? 'Remove')); ?>
                </button>
            </div>
        </div>
        <?php
    }

    /**
     * {@inheritdoc}
     */
    public function sanitize(mixed $value, array $field): mixed
    {
        $allowedTypes = self::resolveAllowedTypes($field);
        return Sanitizer::media($value, $allowedTypes);
    }

    /**
     * {@inheritdoc}
     */
    public function enqueueAssets(array $field): void
    {
        AssetManager::enqueueMediaDependencies();
    }
}
