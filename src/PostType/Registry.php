<?php

declare(strict_types=1);

namespace YourVendor\ThemeFramework\PostType;

use InvalidArgumentException;

/**
 * Registry for custom post types and their associated asset configurations.
 */
class Registry
{
    /**
     * Registered post types.
     *
     * @var array<string, array<string, mixed>>
     */
    protected static array $postTypes = [];

    /**
     * Registered post type assets.
     *
     * @var array<string, array<string, mixed>>
     */
    protected static array $assets = [];

    /**
     * Register a post type configuration.
     *
     * @param string $postType Slug.
     * @param array<string, mixed> $args Standard register_post_type args.
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     */
    public static function register(string $postType, array $args): array
    {
        $postType = trim($postType);
        if ($postType === '') {
            throw new InvalidArgumentException('Post type slug cannot be empty.');
        }

        $label = (string) ($args['label'] ?? ucfirst($postType));

        // Generate complete labels if not provided
        $defaultLabels = [
            'name' => $label,
            'singular_name' => $args['singular_label'] ?? $label,
            'add_new' => __('Add New', 'wp-theme-framework'),
            'add_new_item' => sprintf(__('Add New %s', 'wp-theme-framework'), $args['singular_label'] ?? $label),
            'edit_item' => sprintf(__('Edit %s', 'wp-theme-framework'), $args['singular_label'] ?? $label),
            'new_item' => sprintf(__('New %s', 'wp-theme-framework'), $args['singular_label'] ?? $label),
            'view_item' => sprintf(__('View %s', 'wp-theme-framework'), $args['singular_label'] ?? $label),
            'search_items' => sprintf(__('Search %s', 'wp-theme-framework'), $label),
            'not_found' => sprintf(__('No %s found', 'wp-theme-framework'), strtolower($label)),
            'not_found_in_trash' => sprintf(__('No %s found in Trash', 'wp-theme-framework'), strtolower($label)),
        ];

        $labels = array_merge($defaultLabels, (array) ($args['labels'] ?? []));

        $defaults = [
            'labels' => $labels,
            'public' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'supports' => ['title', 'editor', 'thumbnail'],
            'has_archive' => true,
            'show_in_rest' => true,
        ];

        $postTypeArgs = array_merge($defaults, $args);
        $postTypeArgs['labels'] = $labels;

        /**
         * Filter post type arguments before registration.
         *
         * @param array $postTypeArgs
         * @param string $postType
         */
        $postTypeArgs = (array) apply_filters('theme_framework_post_type_args', $postTypeArgs, $postType);

        self::$postTypes[$postType] = $postTypeArgs;
        return $postTypeArgs;
    }

    /**
     * Register assets for a post type.
     *
     * @param string $postType
     * @param array<string, mixed> $assets
     * @return void
     */
    public static function registerAssets(string $postType, array $assets): void
    {
        self::$assets[$postType] = array_merge_recursive(
            self::$assets[$postType] ?? [],
            $assets
        );
    }

    /**
     * Retrieve post type configuration.
     *
     * @param string $postType
     * @return array<string, mixed>|null
     */
    public static function get(string $postType): ?array
    {
        return self::$postTypes[$postType] ?? null;
    }

    /**
     * Retrieve all registered post types.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function all(): array
    {
        return self::$postTypes;
    }

    /**
     * Retrieve registered assets for a post type.
     *
     * @param string $postType
     * @return array<string, mixed>
     */
    public static function getAssets(string $postType): array
    {
        return self::$assets[$postType] ?? [];
    }

    /**
     * Retrieve all registered post type assets.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function allAssets(): array
    {
        return self::$assets;
    }

    /**
     * Reset registry.
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$postTypes = [];
        self::$assets = [];
    }
}
