<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework;

use KulangaraAlwinJoy\ThemeFramework\Assets\AssetManager;
use KulangaraAlwinJoy\ThemeFramework\Field\Field;
use KulangaraAlwinJoy\ThemeFramework\Field\FieldRegistry;
use KulangaraAlwinJoy\ThemeFramework\Field\FieldResolver;
use KulangaraAlwinJoy\ThemeFramework\Field\FieldSaver;
use KulangaraAlwinJoy\ThemeFramework\Field\FieldType;
use KulangaraAlwinJoy\ThemeFramework\Field\Types\CheckboxField;
use KulangaraAlwinJoy\ThemeFramework\Field\Types\CustomField;
use KulangaraAlwinJoy\ThemeFramework\Field\Types\EmailField;
use KulangaraAlwinJoy\ThemeFramework\Field\Types\GalleryField;
use KulangaraAlwinJoy\ThemeFramework\Field\Types\MediaField;
use KulangaraAlwinJoy\ThemeFramework\Field\Types\NumberField;
use KulangaraAlwinJoy\ThemeFramework\Field\Types\SelectField;
use KulangaraAlwinJoy\ThemeFramework\Field\Types\TextareaField;
use KulangaraAlwinJoy\ThemeFramework\Field\Types\TextField;
use KulangaraAlwinJoy\ThemeFramework\Field\Types\UrlField;
use KulangaraAlwinJoy\ThemeFramework\Group\FieldGroup;
use KulangaraAlwinJoy\ThemeFramework\Menu\MenuField;
use KulangaraAlwinJoy\ThemeFramework\Option\OptionPage;
use KulangaraAlwinJoy\ThemeFramework\PostType\PostType;
use KulangaraAlwinJoy\ThemeFramework\REST\RestSupport;

/**
 * Main Framework Bootstrapper and lifecycle coordinator.
 */
class Framework
{
    /**
     * Booted status flag.
     *
     * @var bool
     */
    protected static bool $booted = false;

    /**
     * Framework version.
     */
    public const VERSION = '1.0.0';

    /**
     * Bootstrap the framework. Idempotent.
     *
     * @return void
     */
    public static function boot(): void
    {
        if (self::$booted) {
            return;
        }

        self::$booted = true;

        // 1. Register core built-in field types
        self::registerDefaultFieldTypes();

        // 2. Attach WordPress lifecycle hooks
        self::registerHooks();

        /**
         * Fires immediately after Theme Framework is fully initialized.
         */
        do_action('theme_framework_loaded');
    }

    /**
     * Check if the framework has been booted.
     *
     * @return bool
     */
    public static function isBooted(): bool
    {
        return self::$booted;
    }

    /**
     * Reset boot state (primarily for isolated unit tests).
     *
     * @return void
     */
    public static function resetBoot(): void
    {
        self::$booted = false;
    }

    /**
     * Register default built-in field types with FieldType registry.
     *
     * @return void
     */
    protected static function registerDefaultFieldTypes(): void
    {
        FieldType::register('text', TextField::class);
        FieldType::register('textarea', TextareaField::class);
        FieldType::register('number', NumberField::class);
        FieldType::register('url', UrlField::class);
        FieldType::register('email', EmailField::class);
        FieldType::register('checkbox', CheckboxField::class);
        FieldType::register('select', SelectField::class);
        FieldType::register('media', MediaField::class);
        FieldType::register('gallery', GalleryField::class);
        FieldType::register('custom', CustomField::class);
    }

    /**
     * Register shared WordPress hooks.
     *
     * @return void
     */
    protected static function registerHooks(): void
    {
        if (!function_exists('add_action')) {
            return;
        }

        // Post Types & REST
        add_action('init', [PostType::class, 'registerAll'], 10);
        add_action('init', [RestSupport::class, 'registerRestFields'], 15);

        // Option Pages & Settings
        add_action('admin_menu', [OptionPage::class, 'registerAdminMenus'], 10);
        add_action('admin_init', [OptionPage::class, 'registerSettings'], 10);

        // Post Meta Boxes
        add_action('add_meta_boxes', [FieldGroup::class, 'registerMetaBoxes'], 10, 1);

        // Post Meta Saving
        add_action('save_post', [self::class, 'handleSavePost'], 10, 2);

        // Menu item fields rendering & saving
        add_action('wp_nav_menu_item_custom_fields', [MenuField::class, 'renderItemFields'], 10, 4);
        add_action('wp_update_nav_menu_item', [MenuField::class, 'saveItemFields'], 10, 3);

        // Asset Management
        add_action('admin_enqueue_scripts', [AssetManager::class, 'handleAdminAssets'], 10, 1);
        add_action('wp_enqueue_scripts', [AssetManager::class, 'handleFrontendAssets'], 10);
    }

    /**
     * Hook callback for saving post meta on save_post.
     *
     * @param int $postId
     * @param mixed $post
     * @return void
     */
    public static function handleSavePost(int $postId, mixed $post = null): void
    {
        $postType = is_object($post) && isset($post->post_type) ? (string) $post->post_type : get_post_type($postId);
        if (!$postType) {
            return;
        }

        $fields = FieldResolver::forPostType($postType);
        if (empty($fields)) {
            return;
        }

        $saver = new FieldSaver();
        $saver->save(
            $postId,
            $fields,
            Field::getStorage(),
            $_POST, // phpcs:ignore WordPress.Security.NonceVerification.Missing
            'post_fields_' . $postId,
            '_theme_framework_nonce'
        );
    }
}
