<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Assets;

use KulangaraAlwinJoy\ThemeFramework\Field\FieldRegistry;
use KulangaraAlwinJoy\ThemeFramework\Field\FieldResolver;
use KulangaraAlwinJoy\ThemeFramework\Menu\MenuFieldRegistry;
use KulangaraAlwinJoy\ThemeFramework\Option\OptionRegistry;
use KulangaraAlwinJoy\ThemeFramework\PostType\Registry as PostTypeRegistry;

/**
 * Manages registration and conditional loading of scripts and stylesheets.
 */
class AssetManager
{
    /**
     * Custom asset base URL.
     *
     * @var string|null
     */
    protected static ?string $assetUrl = null;

    /**
     * Framework version string for cache busting.
     */
    public const VERSION = '1.0.0';

    /**
     * Set a custom asset base URL.
     *
     * @param string $url
     * @return void
     */
    public static function setAssetUrl(string $url): void
    {
        self::$assetUrl = rtrim($url, '/');
    }

    /**
     * Get the base URL for framework assets.
     *
     * @param string $path
     * @return string
     */
    public static function getAssetUrl(string $path = ''): string
    {
        if (self::$assetUrl !== null) {
            $base = self::$assetUrl;
        } else {
            // Automatically detect asset URL relative to theme
            $themeDir = function_exists('get_template_directory') ? get_template_directory() : '';
            $themeUri = function_exists('get_template_directory_uri') ? get_template_directory_uri() : '';

            $frameworkPath = dirname(__DIR__, 2);
            if ($themeDir !== '' && str_starts_with($frameworkPath, $themeDir)) {
                $relative = substr($frameworkPath, strlen($themeDir));
                $base = rtrim($themeUri . $relative . '/assets', '/');
            } else {
                $base = content_url('/vendor/kulangaraalwinjoy/wp-theme-framework/assets');
            }
        }

        $base = (string) apply_filters('theme_framework_assets_url', $base);

        return $path !== '' ? $base . '/' . ltrim($path, '/') : $base;
    }

    /**
     * Register a CSS stylesheet with WordPress.
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
        string|bool|null $ver = self::VERSION,
        string $media = 'all'
    ): void {
        wp_register_style($handle, $src, $deps, $ver, $media);
    }

    /**
     * Register a JavaScript script with WordPress.
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
        string|bool|null $ver = self::VERSION,
        bool $inFooter = true
    ): void {
        wp_register_script($handle, $src, $deps, $ver, $inFooter);
    }

    /**
     * Enqueue a registered style or directly with src.
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
        string|bool|null $ver = self::VERSION,
        string $media = 'all'
    ): void {
        wp_enqueue_style($handle, $src, $deps, $ver, $media);
    }

    /**
     * Enqueue a registered script or directly with src.
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
        string|bool|null $ver = self::VERSION,
        bool $inFooter = true
    ): void {
        wp_enqueue_script($handle, $src, $deps, $ver, $inFooter);
    }

    /**
     * Register all internal framework assets.
     *
     * @return void
     */
    public static function registerFrameworkAssets(): void
    {
        // Admin Base Styles & Scripts
        self::registerStyle(
            'theme-framework-admin',
            self::getAssetUrl('admin/css/framework-admin.css')
        );

        self::registerScript(
            'theme-framework-admin',
            self::getAssetUrl('admin/js/framework-admin.js'),
            ['jquery']
        );

        // Conditions Engine
        self::registerScript(
            'theme-framework-conditions',
            self::getAssetUrl('admin/js/conditions.js'),
            ['jquery']
        );

        // Media Field
        self::registerStyle(
            'theme-framework-media',
            self::getAssetUrl('admin/css/media-field.css'),
            ['theme-framework-admin']
        );

        self::registerScript(
            'theme-framework-media',
            self::getAssetUrl('admin/js/media-field.js'),
            ['jquery']
        );

        // Gallery Field
        self::registerStyle(
            'theme-framework-gallery',
            self::getAssetUrl('admin/css/gallery-field.css'),
            ['theme-framework-admin']
        );

        self::registerScript(
            'theme-framework-gallery',
            self::getAssetUrl('admin/js/gallery-field.js'),
            ['jquery', 'jquery-ui-sortable']
        );
    }

    /**
     * Enqueue media modal dependencies strictly when required.
     *
     * @return void
     */
    public static function enqueueMediaDependencies(): void
    {
        if (function_exists('wp_enqueue_media')) {
            wp_enqueue_media();
        }

        self::enqueueStyle('theme-framework-media');
        self::enqueueScript('theme-framework-media');
    }

    /**
     * Enqueue gallery dependencies strictly when required.
     *
     * @return void
     */
    public static function enqueueGalleryDependencies(): void
    {
        if (function_exists('wp_enqueue_media')) {
            wp_enqueue_media();
        }

        wp_enqueue_script('jquery-ui-sortable');
        self::enqueueStyle('theme-framework-gallery');
        self::enqueueScript('theme-framework-gallery');
    }

    /**
     * Handle conditional admin asset enqueuing.
     *
     * @param string $hookSuffix
     * @return void
     */
    public static function handleAdminAssets(string $hookSuffix): void
    {
        self::registerFrameworkAssets();

        if (!function_exists('get_current_screen')) {
            return;
        }

        $screen = get_current_screen();
        if (!$screen) {
            return;
        }

        // 1. Post Edit Screen
        if ($screen->base === 'post' && !empty($screen->post_type)) {
            $postType = (string) $screen->post_type;
            $fields = FieldResolver::forPostType($postType);

            if (!empty($fields)) {
                self::enqueueStyle('theme-framework-admin');
                self::enqueueScript('theme-framework-admin');

                if (FieldResolver::hasConditions($fields)) {
                    self::enqueueScript('theme-framework-conditions');
                }

                if (FieldResolver::hasFieldType($fields, 'media')) {
                    self::enqueueMediaDependencies();
                }

                if (FieldResolver::hasFieldType($fields, 'gallery')) {
                    self::enqueueGalleryDependencies();
                }

                // Enqueue custom field-defined assets
                foreach ($fields as $field) {
                    self::enqueueCustomAssets($field['assets']['admin'] ?? []);
                }
            }

            // Enqueue post type specific admin assets
            $ptAssets = PostTypeRegistry::getAssets($postType);
            self::enqueueCustomAssets($ptAssets['admin'] ?? []);
            return;
        }

        // 2. Nav Menus Screen
        if ($screen->base === 'nav-menus') {
            $menuFields = MenuFieldRegistry::all();
            if (!empty($menuFields)) {
                self::enqueueStyle('theme-framework-admin');
                self::enqueueScript('theme-framework-admin');

                if (FieldResolver::hasFieldType($menuFields, 'media')) {
                    self::enqueueMediaDependencies();
                }

                if (FieldResolver::hasFieldType($menuFields, 'gallery')) {
                    self::enqueueGalleryDependencies();
                }
            }
            return;
        }

        // 3. Option Pages
        $pages = OptionRegistry::getPages();
        foreach ($pages as $pageId => $page) {
            // Screen id matches toplevel_page_{id} or {parent}_page_{id}
            if (str_contains($screen->id, $pageId)) {
                self::enqueueStyle('theme-framework-admin');
                self::enqueueScript('theme-framework-admin');

                $pageOptions = OptionRegistry::getOptions($pageId);
                if (FieldResolver::hasFieldType($pageOptions, 'media')) {
                    self::enqueueMediaDependencies();
                }
                if (FieldResolver::hasFieldType($pageOptions, 'gallery')) {
                    self::enqueueGalleryDependencies();
                }
                break;
            }
        }
    }

    /**
     * Handle conditional frontend asset enqueuing.
     *
     * @return void
     */
    public static function handleFrontendAssets(): void
    {
        if (function_exists('is_singular') && is_singular()) {
            $post = get_post();
            if ($post && !empty($post->post_type)) {
                $ptAssets = PostTypeRegistry::getAssets((string) $post->post_type);
                self::enqueueCustomAssets($ptAssets['frontend'] ?? []);
            }
        }
    }

    /**
     * Enqueue a list of handles or asset descriptors.
     *
     * @param array<string, mixed> $assets
     * @return void
     */
    protected static function enqueueCustomAssets(array $assets): void
    {
        if (!empty($assets['css'])) {
            foreach ((array) $assets['css'] as $handle) {
                if (is_string($handle)) {
                    wp_enqueue_style($handle);
                }
            }
        }

        if (!empty($assets['js'])) {
            foreach ((array) $assets['js'] as $handle) {
                if (is_string($handle)) {
                    wp_enqueue_script($handle);
                }
            }
        }
    }
}
