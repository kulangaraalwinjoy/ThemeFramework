<?php

declare(strict_types=1);

namespace YourVendor\ThemeFramework\Assets;

/**
 * Public API Facade for script and style management.
 */
class Assets
{
    /**
     * Register a CSS stylesheet.
     *
     * @param string $handle
     * @param string $src
     * @param array<string> $deps
     * @param string|bool|null $ver
     * @param string $media
     * @return void
     */
    public static function registerStyle(
        string $handle,
        string $src,
        array $deps = [],
        string|bool|null $ver = false,
        string $media = 'all'
    ): void {
        AssetManager::registerStyle($handle, $src, $deps, $ver, $media);
    }

    /**
     * Register a JavaScript script.
     *
     * @param string $handle
     * @param string $src
     * @param array<string> $deps
     * @param string|bool|null $ver
     * @param bool $inFooter
     * @return void
     */
    public static function registerScript(
        string $handle,
        string $src,
        array $deps = [],
        string|bool|null $ver = false,
        bool $inFooter = true
    ): void {
        AssetManager::registerScript($handle, $src, $deps, $ver, $inFooter);
    }

    /**
     * Enqueue a stylesheet.
     *
     * @param string $handle
     * @param string $src
     * @param array<string> $deps
     * @param string|bool|null $ver
     * @param string $media
     * @return void
     */
    public static function enqueueStyle(
        string $handle,
        string $src = '',
        array $deps = [],
        string|bool|null $ver = false,
        string $media = 'all'
    ): void {
        AssetManager::enqueueStyle($handle, $src, $deps, $ver, $media);
    }

    /**
     * Enqueue a script.
     *
     * @param string $handle
     * @param string $src
     * @param array<string> $deps
     * @param string|bool|null $ver
     * @param bool $inFooter
     * @return void
     */
    public static function enqueueScript(
        string $handle,
        string $src = '',
        array $deps = [],
        string|bool|null $ver = false,
        bool $inFooter = true
    ): void {
        AssetManager::enqueueScript($handle, $src, $deps, $ver, $inFooter);
    }
}
