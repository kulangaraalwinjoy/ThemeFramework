<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Tests;

/**
 * State store for WordPress mock functions in tests.
 */
class WordPressMockState
{
    /** @var array<string, array<int, array{callback: callable, priority: int, accepted_args: int}>> */
    public static array $actions = [];

    /** @var array<string, array<int, array{callback: callable, priority: int, accepted_args: int}>> */
    public static array $filters = [];

    /** @var array<string, mixed> */
    public static array $options = [];

    /** @var array<int, array<string, mixed>> */
    public static array $postMeta = [];

    /** @var array<int, object> */
    public static array $posts = [];

    /** @var array<int, array<string, mixed>> */
    public static array $attachmentMetadata = [];

    /** @var array<string, bool> */
    public static array $capabilities = [];

    /** @var array<string, array<string, mixed>> */
    public static array $postTypes = [];

    /** @var array<string, array<string, mixed>> */
    public static array $registeredMeta = [];

    /** @var array<string, array<string, mixed>> */
    public static array $registeredSettings = [];

    /** @var array<string, array<string, mixed>> */
    public static array $settingsSections = [];

    /** @var array<string, array<string, mixed>> */
    public static array $settingsFields = [];

    /** @var array<string, array<string, mixed>> */
    public static array $metaBoxes = [];

    /** @var array<string, array<string, mixed>> */
    public static array $menuPages = [];

    /** @var array<string, array<string, mixed>> */
    public static array $submenuPages = [];

    /** @var array<string, array<string, mixed>> */
    public static array $styles = [];

    /** @var array<string, array<string, mixed>> */
    public static array $scripts = [];

    /** @var array<string, bool> */
    public static array $enqueuedStyles = [];

    /** @var array<string, bool> */
    public static array $enqueuedScripts = [];

    public static bool $mediaEnqueued = false;

    public static ?object $currentScreen = null;

    public static int $currentPostId = 1;

    public static function reset(): void
    {
        self::$actions = [];
        self::$filters = [];
        self::$options = [];
        self::$postMeta = [];
        self::$posts = [];
        self::$attachmentMetadata = [];
        self::$capabilities = [
            'edit_post' => true,
            'manage_options' => true,
            'edit_theme_options' => true,
            'unfiltered_html' => true,
        ];
        self::$postTypes = [];
        self::$registeredMeta = [];
        self::$registeredSettings = [];
        self::$settingsSections = [];
        self::$settingsFields = [];
        self::$metaBoxes = [];
        self::$menuPages = [];
        self::$submenuPages = [];
        self::$styles = [];
        self::$scripts = [];
        self::$enqueuedStyles = [];
        self::$enqueuedScripts = [];
        self::$mediaEnqueued = false;
        self::$currentScreen = null;
        self::$currentPostId = 1;
    }
}
