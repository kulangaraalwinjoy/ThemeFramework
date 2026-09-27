<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Security;

/**
 * Handles WordPress user capability checks.
 */
class Capability
{
    /**
     * Check if current user has the specified capability.
     *
     * @param string $capability
     * @param mixed ...$args
     * @return bool
     */
    public static function can(string $capability, mixed ...$args): bool
    {
        return current_user_can($capability, ...$args);
    }

    /**
     * Check if user can edit a specific post.
     *
     * @param int $postId
     * @return bool
     */
    public static function canEditPost(int $postId): bool
    {
        return self::can('edit_post', $postId);
    }

    /**
     * Check if user can edit theme options (e.g. menus).
     *
     * @return bool
     */
    public static function canEditThemeOptions(): bool
    {
        return self::can('edit_theme_options');
    }

    /**
     * Check if user can manage options (default for option pages).
     *
     * @param string $capability
     * @return bool
     */
    public static function canManageOptions(string $capability = 'manage_options'): bool
    {
        return self::can($capability);
    }

    /**
     * Check if user can publish raw HTML / unrestricted JS/CSS.
     *
     * @return bool
     */
    public static function canUnfilteredHtml(): bool
    {
        return self::can('unfiltered_html');
    }
}
