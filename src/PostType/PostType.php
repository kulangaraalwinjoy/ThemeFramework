<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\PostType;

/**
 * Public API Facade for Custom Post Type registration and asset binding.
 */
class PostType
{
    /**
     * Register a new post type.
     *
     * @param string $postType Slug.
     * @param array<string, mixed> $args Arguments passed to register_post_type.
     * @return void
     */
    public static function register(string $postType, array $args = []): void
    {
        Registry::register($postType, $args);
    }

    /**
     * Configure assets for a post type (admin and frontend).
     *
     * @param string $postType
     * @param array<string, mixed> $assets
     * @return void
     */
    public static function assets(string $postType, array $assets): void
    {
        Registry::registerAssets($postType, $assets);
    }

    /**
     * Hook callback for registering all configured post types with WordPress.
     *
     * @return void
     */
    public static function registerAll(): void
    {
        $types = Registry::all();

        foreach ($types as $postType => $args) {
            $slug = strtolower(trim((string) $postType));
            if ($slug !== '') {
                register_post_type($slug, $args);
            }
        }
    }
}
