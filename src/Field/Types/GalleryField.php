<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Field\Types;

use KulangaraAlwinJoy\ThemeFramework\Assets\AssetManager;
use KulangaraAlwinJoy\ThemeFramework\Security\Sanitizer;

/**
 * Gallery multi-image field type supporting add, remove, and reorder.
 */
class GalleryField extends BaseFieldType
{
    /**
     * {@inheritdoc}
     */
    public function render(string $name, mixed $value, array $field): void
    {
        $id = esc_attr((string) ($field['id'] ?? $name));
        $allowedTypes = MediaField::resolveAllowedTypes($field);

        $attachmentIds = [];
        if (is_array($value)) {
            $attachmentIds = array_map('absint', $value);
        } elseif (is_string($value) && $value !== '') {
            if (str_starts_with(trim($value), '[')) {
                $decoded = json_decode($value, true);
                $attachmentIds = is_array($decoded) ? array_map('absint', $decoded) : [];
            } else {
                $attachmentIds = array_map('absint', explode(',', $value));
            }
        }

        $attachmentIds = array_filter($attachmentIds, fn ($imgId) => $imgId > 0);
        $idsString = implode(',', $attachmentIds);
        $allowedJson = !empty($allowedTypes) ? esc_attr((string) wp_json_encode($allowedTypes)) : '';

        ?>
        <div class="theme-framework-gallery-wrap" data-allowed-types="<?php echo $allowedJson; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>" data-title="<?php echo esc_attr((string) ($field['modal_title'] ?? $field['label'] ?? 'Add Images to Gallery')); ?>" data-button="<?php echo esc_attr((string) ($field['button_text'] ?? 'Add to Gallery')); ?>">
            <input type="hidden" id="<?php echo esc_attr($id); ?>" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($idsString); ?>" class="theme-framework-gallery-ids" />

            <ul class="theme-framework-gallery-list">
                <?php foreach ($attachmentIds as $attachmentId) : ?>
                    <?php
                    $url = wp_get_attachment_image_url($attachmentId, 'thumbnail');
                    if (!$url) {
                        $url = wp_get_attachment_url($attachmentId);
                    }
                    if (!$url) {
                        continue;
                    }
                    ?>
                    <li class="theme-framework-gallery-item" data-id="<?php echo (int) $attachmentId; ?>">
                        <img src="<?php echo esc_url((string) $url); ?>" alt="" />
                        <button type="button" class="theme-framework-gallery-remove" title="<?php esc_attr_e('Remove image', 'wp-theme-framework'); ?>">&times;</button>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="theme-framework-gallery-actions">
                <button type="button" class="button button-secondary theme-framework-gallery-add">
                    <?php echo esc_html((string) ($field['add_button'] ?? 'Add Images')); ?>
                </button>
                <button type="button" class="button button-link-delete theme-framework-gallery-clear" style="<?php echo empty($attachmentIds) ? 'display: none;' : ''; ?>">
                    <?php echo esc_html((string) ($field['clear_button'] ?? 'Clear Gallery')); ?>
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
        $allowedTypes = MediaField::resolveAllowedTypes($field);
        return Sanitizer::gallery($value, $allowedTypes);
    }

    /**
     * {@inheritdoc}
     */
    public function enqueueAssets(array $field): void
    {
        AssetManager::enqueueGalleryDependencies();
    }
}
