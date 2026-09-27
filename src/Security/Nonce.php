<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Security;

/**
 * Handles WordPress nonce generation, rendering, and verification.
 */
class Nonce
{
    /**
     * Default action prefix.
     */
    public const PREFIX = 'theme_framework_';

    /**
     * Create a cryptographic nonce for an action.
     *
     * @param string $action
     * @return string
     */
    public static function create(string $action): string
    {
        return wp_create_nonce(self::PREFIX . $action);
    }

    /**
     * Verify a cryptographic nonce for an action.
     *
     * @param string|null $nonce
     * @param string $action
     * @return bool
     */
    public static function verify(?string $nonce, string $action): bool
    {
        if (empty($nonce)) {
            return false;
        }

        return (bool) wp_verify_nonce($nonce, self::PREFIX . $action);
    }

    /**
     * Render or return a nonce hidden field.
     *
     * @param string $action
     * @param string $name
     * @param bool $referer
     * @param bool $echo
     * @return string
     */
    public static function field(
        string $action,
        string $name = '_theme_framework_nonce',
        bool $referer = true,
        bool $echo = true
    ): string {
        return wp_nonce_field(self::PREFIX . $action, $name, $referer, $echo);
    }
}
