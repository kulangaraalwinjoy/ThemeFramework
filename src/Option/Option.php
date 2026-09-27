<?php

declare(strict_types=1);

namespace YourVendor\ThemeFramework\Option;

use YourVendor\ThemeFramework\Field\FieldType;
use YourVendor\ThemeFramework\Storage\OptionStorage;

/**
 * Public API Facade for theme options registration, querying, and manipulation.
 */
class Option
{
    /**
     * Option storage instance.
     *
     * @var OptionStorage|null
     */
    protected static ?OptionStorage $storage = null;

    /**
     * Get the storage instance.
     *
     * @return OptionStorage
     */
    public static function getStorage(): OptionStorage
    {
        if (self::$storage === null) {
            self::$storage = new OptionStorage();
        }

        return self::$storage;
    }

    /**
     * Set custom storage instance.
     *
     * @param OptionStorage|null $storage
     * @return void
     */
    public static function setStorage(?OptionStorage $storage): void
    {
        self::$storage = $storage;
    }

    /**
     * Register a new theme option.
     *
     * @param string $id Option identifier.
     * @param array<string, mixed> $config Configuration array.
     * @return void
     */
    public static function add(string $id, array $config): void
    {
        OptionRegistry::addOption($id, $config);
    }

    /**
     * Retrieve the stored value of an option.
     *
     * @param string $id
     * @param mixed $default
     * @return mixed
     */
    public static function get(string $id, mixed $default = null): mixed
    {
        $value = self::getStorage()->get($id, null, $default);
        $option = OptionRegistry::getOption($id) ?? ['id' => $id];

        /**
         * Filter retrieved option value.
         *
         * @param mixed $value
         * @param array $option
         */
        return apply_filters('theme_framework_option_value', $value, $option);
    }

    /**
     * Update an option value.
     *
     * @param string $id
     * @param mixed $value
     * @return bool
     */
    public static function set(string $id, mixed $value): bool
    {
        $option = OptionRegistry::getOption($id);
        if ($option) {
            $type = (string) ($option['type'] ?? 'text');
            if (FieldType::has($type)) {
                $handler = FieldType::get($type);
                $value = $handler->sanitize($value, $option);
            }
        }

        return self::getStorage()->set($id, $value);
    }

    /**
     * Delete an option value.
     *
     * @param string $id
     * @return bool
     */
    public static function delete(string $id): bool
    {
        return self::getStorage()->delete($id);
    }

    /**
     * Retrieve URL for a media option attachment.
     *
     * @param string $id
     * @param string $size
     * @return string|null
     */
    public static function url(string $id, string $size = 'full'): ?string
    {
        $attachmentId = self::get($id);
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
     * Retrieve detailed attachment data for a media option.
     *
     * @param string $id
     * @return array<string, mixed>|null
     */
    public static function attachment(string $id): ?array
    {
        $attachmentId = self::get($id);
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
}
