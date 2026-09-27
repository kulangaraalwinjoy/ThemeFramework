<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Field;

use KulangaraAlwinJoy\ThemeFramework\Storage\PostMetaStorage;

/**
 * Public API Facade for post fields registration, querying, and manipulation.
 */
class Field
{
    /**
     * Post meta storage instance.
     *
     * @var PostMetaStorage|null
     */
    protected static ?PostMetaStorage $storage = null;

    /**
     * Get the default storage adapter for post fields.
     *
     * @return PostMetaStorage
     */
    public static function getStorage(): PostMetaStorage
    {
        if (self::$storage === null) {
            self::$storage = new PostMetaStorage();
        }

        return self::$storage;
    }

    /**
     * Set a custom storage adapter (primarily for testing).
     *
     * @param PostMetaStorage|null $storage
     * @return void
     */
    public static function setStorage(?PostMetaStorage $storage): void
    {
        self::$storage = $storage;
    }

    /**
     * Register a new field.
     *
     * @param string $id Unique field identifier.
     * @param array<string, mixed> $config Field configuration options.
     * @return void
     */
    public static function add(string $id, array $config): void
    {
        FieldRegistry::add($id, $config);
    }

    /**
     * Retrieve the saved value of a field for a post.
     *
     * @param string $id Field identifier.
     * @param int|null $postId Target post ID (defaults to current post).
     * @param mixed $default Fallback value if field is unset.
     * @return mixed
     */
    public static function get(string $id, ?int $postId = null, mixed $default = null): mixed
    {
        $value = self::getStorage()->get($id, $postId, $default);
        $field = FieldRegistry::get($id) ?? ['id' => $id];

        /**
         * Filter the retrieved field value.
         *
         * @param mixed $value
         * @param array $field
         * @param int|null $postId
         */
        return apply_filters('theme_framework_field_value', $value, $field, $postId);
    }

    /**
     * Set the value of a field for a post.
     *
     * @param string $id
     * @param int $postId
     * @param mixed $value
     * @return bool
     */
    public static function set(string $id, int $postId, mixed $value): bool
    {
        $field = FieldRegistry::get($id);
        if ($field) {
            $type = (string) ($field['type'] ?? 'text');
            if (FieldType::has($type)) {
                $handler = FieldType::get($type);
                $value = $handler->sanitize($value, $field);
            }
        }

        return self::getStorage()->set($id, $value, $postId);
    }

    /**
     * Delete the field value for a post.
     *
     * @param string $id
     * @param int $postId
     * @return bool
     */
    public static function delete(string $id, int $postId): bool
    {
        return self::getStorage()->delete($id, $postId);
    }

    /**
     * Retrieve URL for a media field attachment.
     *
     * @param string $id
     * @param int|null $postId
     * @param string $size
     * @return string|null
     */
    public static function url(string $id, ?int $postId = null, string $size = 'full'): ?string
    {
        $attachmentId = self::get($id, $postId);
        if (empty($attachmentId) || !is_numeric($attachmentId)) {
            return null;
        }

        $url = wp_get_attachment_image_url((int) $attachmentId, $size);
        if ($url) {
            return (string) $url;
        }

        $fallback = wp_get_attachment_url((int) $attachmentId);
        return $fallback ? (string) $fallback : null;
    }

    /**
     * Retrieve detailed attachment data for a media field.
     *
     * @param string $id
     * @param int|null $postId
     * @return array<string, mixed>|null
     */
    public static function attachment(string $id, ?int $postId = null): ?array
    {
        $attachmentId = self::get($id, $postId);
        if (empty($attachmentId) || !is_numeric($attachmentId)) {
            return null;
        }

        $idInt = (int) $attachmentId;
        $post = get_post($idInt);
        if (!$post) {
            return null;
        }

        $url = wp_get_attachment_url($idInt);
        $alt = get_post_meta($idInt, '_wp_attachment_image_alt', true);
        $metadata = wp_get_attachment_metadata($idInt);

        $width = is_array($metadata) && isset($metadata['width']) ? (int) $metadata['width'] : null;
        $height = is_array($metadata) && isset($metadata['height']) ? (int) $metadata['height'] : null;

        return [
            'id' => $idInt,
            'url' => $url ?: '',
            'title' => $post->post_title ?? '',
            'caption' => $post->post_excerpt ?? '',
            'description' => $post->post_content ?? '',
            'alt' => is_string($alt) ? $alt : '',
            'mime_type' => $post->post_mime_type ?? '',
            'width' => $width,
            'height' => $height,
        ];
    }

    /**
     * Retrieve array of image URLs or attachment data for a gallery field.
     *
     * @param string $id
     * @param int|null $postId
     * @param string $size
     * @return array<int, string>
     */
    public static function images(string $id, ?int $postId = null, string $size = 'full'): array
    {
        $ids = self::get($id, $postId, []);
        if (empty($ids)) {
            return [];
        }

        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        if (!is_array($ids)) {
            return [];
        }

        $urls = [];
        foreach ($ids as $rawId) {
            $attId = absint($rawId);
            if ($attId <= 0) {
                continue;
            }

            $url = wp_get_attachment_image_url($attId, $size);
            if (!$url) {
                $url = wp_get_attachment_url($attId);
            }

            if ($url) {
                $urls[] = (string) $url;
            }
        }

        return $urls;
    }
}
