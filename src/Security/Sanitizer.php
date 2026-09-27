<?php

declare(strict_types=1);

namespace YourVendor\ThemeFramework\Security;

/**
 * Robust sanitization helper for field and option values.
 */
class Sanitizer
{
    /**
     * Sanitize a text value.
     *
     * @param mixed $value
     * @return string
     */
    public static function text(mixed $value): string
    {
        return sanitize_text_field((string) $value);
    }

    /**
     * Sanitize a textarea value.
     *
     * @param mixed $value
     * @return string
     */
    public static function textarea(mixed $value): string
    {
        return sanitize_textarea_field((string) $value);
    }

    /**
     * Sanitize a numeric value.
     *
     * @param mixed $value
     * @param bool $allowFloat
     * @return int|float|null
     */
    public static function number(mixed $value, bool $allowFloat = false): int|float|null
    {
        if ($value === '' || $value === null) {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        return $allowFloat ? (float) $value : (int) $value;
    }

    /**
     * Sanitize a URL value.
     *
     * @param mixed $value
     * @return string
     */
    public static function url(mixed $value): string
    {
        return esc_url_raw((string) $value);
    }

    /**
     * Sanitize an email value.
     *
     * @param mixed $value
     * @return string
     */
    public static function email(mixed $value): string
    {
        return sanitize_email((string) $value);
    }

    /**
     * Normalize a checkbox value to boolean integer (1 or 0).
     *
     * @param mixed $value
     * @return int
     */
    public static function checkbox(mixed $value): int
    {
        return (!empty($value) && $value !== '0' && $value !== false) ? 1 : 0;
    }

    /**
     * Sanitize a select option against allowed choices.
     *
     * @param mixed $value
     * @param array<string|int, mixed> $allowedChoices
     * @param bool $multiple
     * @return string|int|array<int, string|int>|null
     */
    public static function select(mixed $value, array $allowedChoices, bool $multiple = false): string|int|array|null
    {
        $allowedKeys = array_map('strval', array_keys($allowedChoices));

        if ($multiple) {
            if (!is_array($value)) {
                return [];
            }
            $sanitized = [];
            foreach ($value as $item) {
                $strItem = (string) $item;
                if (in_array($strItem, $allowedKeys, true)) {
                    $sanitized[] = sanitize_text_field($strItem);
                }
            }
            return $sanitized;
        }

        $strValue = (string) $value;
        if (in_array($strValue, $allowedKeys, true)) {
            return sanitize_text_field($strValue);
        }

        return null;
    }

    /**
     * Sanitize and validate a media attachment ID server-side.
     *
     * @param mixed $value
     * @param array<string> $allowedMimes
     * @return int|null
     */
    public static function media(mixed $value, array $allowedMimes = []): ?int
    {
        $attachmentId = absint($value);
        if ($attachmentId === 0) {
            return null;
        }

        // Verify attachment exists and is an attachment post type
        $post = get_post($attachmentId);
        if (!$post || !is_object($post) || $post->post_type !== 'attachment') {
            return null;
        }

        // Verify MIME type if restrictions are specified
        if (!empty($allowedMimes)) {
            $postMime = get_post_mime_type($attachmentId);
            if (!$postMime || !in_array($postMime, $allowedMimes, true)) {
                return null;
            }
        }

        return $attachmentId;
    }

    /**
     * Sanitize and validate an array of gallery attachment IDs server-side.
     *
     * @param mixed $value
     * @param array<string> $allowedMimes
     * @return array<int>
     */
    public static function gallery(mixed $value, array $allowedMimes = []): array
    {
        if (empty($value)) {
            return [];
        }

        if (is_string($value)) {
            // Support comma-separated or JSON list
            if (str_starts_with(trim($value), '[')) {
                $decoded = json_decode($value, true);
                $value = is_array($decoded) ? $decoded : [];
            } else {
                $value = explode(',', $value);
            }
        }

        if (!is_array($value)) {
            return [];
        }

        $validIds = [];
        foreach ($value as $rawId) {
            $id = self::media($rawId, $allowedMimes);
            if ($id !== null && !in_array($id, $validIds, true)) {
                $validIds[] = $id;
            }
        }

        return array_values($validIds);
    }

    /**
     * Sanitize custom CSS.
     *
     * @param mixed $value
     * @return string
     */
    public static function css(mixed $value): string
    {
        if (!Capability::canEditThemeOptions()) {
            return '';
        }

        return wp_strip_all_tags((string) $value);
    }

    /**
     * Sanitize custom JavaScript, strictly guarding unfiltered_html capability.
     *
     * @param mixed $value
     * @return string
     */
    public static function javascript(mixed $value): string
    {
        if (!Capability::canUnfilteredHtml()) {
            // Low-privilege users cannot save raw JavaScript
            return '';
        }

        return (string) $value;
    }
}
