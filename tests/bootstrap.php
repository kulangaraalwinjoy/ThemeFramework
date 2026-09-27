<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once __DIR__ . '/WordPressMockState.php';

use KulangaraAlwinJoy\ThemeFramework\Tests\WordPressMockState;

WordPressMockState::reset();

// Core WordPress helper functions
if (!function_exists('__return_null')) {
    function __return_null(): null
    {
        return null;
    }
}

if (!function_exists('__return_empty_array')) {
    function __return_empty_array(): array
    {
        return [];
    }
}

if (!function_exists('__return_true')) {
    function __return_true(): bool
    {
        return true;
    }
}

if (!function_exists('__return_false')) {
    function __return_false(): bool
    {
        return false;
    }
}

// Hook functions
if (!function_exists('add_action')) {
    function add_action(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void
    {
        WordPressMockState::$actions[$hook][] = [
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args,
        ];
    }
}

if (!function_exists('do_action')) {
    function do_action(string $hook, mixed ...$args): void
    {
        if (empty(WordPressMockState::$actions[$hook])) {
            return;
        }

        foreach (WordPressMockState::$actions[$hook] as $action) {
            call_user_func_array($action['callback'], array_slice($args, 0, $action['accepted_args']));
        }
    }
}

if (!function_exists('apply_filters')) {
    function apply_filters(string $hook, mixed $value, mixed ...$args): mixed
    {
        if (empty(WordPressMockState::$filters[$hook])) {
            return $value;
        }

        foreach (WordPressMockState::$filters[$hook] as $filter) {
            $callArgs = array_merge([$value], array_slice($args, 0, $filter['accepted_args'] - 1));
            $value = call_user_func_array($filter['callback'], $callArgs);
        }

        return $value;
    }
}

if (!function_exists('add_filter')) {
    function add_filter(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void
    {
        WordPressMockState::$filters[$hook][] = [
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $accepted_args,
        ];
    }
}

// Post Type
if (!function_exists('register_post_type')) {
    function register_post_type(string $postType, array $args = []): object
    {
        WordPressMockState::$postTypes[$postType] = $args;
        return (object) ['name' => $postType, 'args' => $args];
    }
}

// Meta & Options
if (!function_exists('get_post_meta')) {
    function get_post_meta(int $postId, string $key = '', bool $single = false): mixed
    {
        if ($key === '') {
            return WordPressMockState::$postMeta[$postId] ?? [];
        }

        $val = WordPressMockState::$postMeta[$postId][$key] ?? ($single ? '' : []);
        return $val;
    }
}

if (!function_exists('update_post_meta')) {
    function update_post_meta(int $postId, string $key, mixed $value): bool
    {
        WordPressMockState::$postMeta[$postId][$key] = $value;
        return true;
    }
}

if (!function_exists('delete_post_meta')) {
    function delete_post_meta(int $postId, string $key): bool
    {
        if (isset(WordPressMockState::$postMeta[$postId][$key])) {
            unset(WordPressMockState::$postMeta[$postId][$key]);
            return true;
        }
        return false;
    }
}

if (!function_exists('metadata_exists')) {
    function metadata_exists(string $type, int $objectId, string $key): bool
    {
        return isset(WordPressMockState::$postMeta[$objectId][$key]);
    }
}

if (!function_exists('get_option')) {
    function get_option(string $option, mixed $default = false): mixed
    {
        return WordPressMockState::$options[$option] ?? $default;
    }
}

if (!function_exists('update_option')) {
    function update_option(string $option, mixed $value): bool
    {
        WordPressMockState::$options[$option] = $value;
        return true;
    }
}

if (!function_exists('delete_option')) {
    function delete_option(string $option): bool
    {
        if (isset(WordPressMockState::$options[$option])) {
            unset(WordPressMockState::$options[$option]);
            return true;
        }
        return false;
    }
}

// Nonce & Capabilities
if (!function_exists('wp_create_nonce')) {
    function wp_create_nonce(string|int $action = -1): string
    {
        return substr(md5('nonce_' . (string) $action), 0, 10);
    }
}

if (!function_exists('wp_verify_nonce')) {
    function wp_verify_nonce(string $nonce, string|int $action = -1): bool|int
    {
        $expected = substr(md5('nonce_' . (string) $action), 0, 10);
        return hash_equals($expected, $nonce) ? 1 : false;
    }
}

if (!function_exists('wp_nonce_field')) {
    function wp_nonce_field(string|int $action = -1, string $name = '_wpnonce', bool $referer = true, bool $echo = true): string
    {
        $nonce = wp_create_nonce($action);
        $html = sprintf('<input type="hidden" id="%s" name="%s" value="%s" />', esc_attr($name), esc_attr($name), esc_attr($nonce));
        if ($echo) {
            echo $html;
        }
        return $html;
    }
}

if (!function_exists('current_user_can')) {
    function current_user_can(string $capability, mixed ...$args): bool
    {
        if (isset(WordPressMockState::$capabilities[$capability])) {
            return WordPressMockState::$capabilities[$capability];
        }
        return true;
    }
}

// Sanitization & Escaping
if (!function_exists('sanitize_text_field')) {
    function sanitize_text_field(string $str): string
    {
        return trim(strip_tags($str));
    }
}

if (!function_exists('sanitize_textarea_field')) {
    function sanitize_textarea_field(string $str): string
    {
        return trim(strip_tags($str));
    }
}

if (!function_exists('sanitize_email')) {
    function sanitize_email(string $email): string
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : '';
    }
}

if (!function_exists('esc_url_raw')) {
    function esc_url_raw(string $url): string
    {
        return filter_var($url, FILTER_VALIDATE_URL) ? $url : (str_starts_with($url, '/') ? $url : '');
    }
}

if (!function_exists('esc_attr')) {
    function esc_attr(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_html')) {
    function esc_html(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_url')) {
    function esc_url(string $url): string
    {
        return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('esc_textarea')) {
    function esc_textarea(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('absint')) {
    function absint(mixed $maybeint): int
    {
        return abs((int) $maybeint);
    }
}

if (!function_exists('wp_strip_all_tags')) {
    function wp_strip_all_tags(string $string, bool $remove_breaks = false): string
    {
        return strip_tags($string);
    }
}

// Settings API & Menus
if (!function_exists('register_setting')) {
    function register_setting(string $optionGroup, string $optionName, array $args = []): void
    {
        WordPressMockState::$registeredSettings[$optionGroup][$optionName] = $args;
    }
}

if (!function_exists('add_settings_section')) {
    function add_settings_section(string $id, string $title, ?callable $callback, string $page): void
    {
        WordPressMockState::$settingsSections[$page][$id] = [
            'title' => $title,
            'callback' => $callback,
        ];
    }
}

if (!function_exists('add_settings_field')) {
    function add_settings_field(string $id, string $title, callable $callback, string $page, string $section = 'default', array $args = []): void
    {
        WordPressMockState::$settingsFields[$page][$id] = [
            'title' => $title,
            'callback' => $callback,
            'section' => $section,
            'args' => $args,
        ];
    }
}

if (!function_exists('add_menu_page')) {
    function add_menu_page(string $pageTitle, string $menuTitle, string $capability, string $menuSlug, ?callable $callback = null, string $iconUrl = '', ?int $position = null): string
    {
        WordPressMockState::$menuPages[$menuSlug] = [
            'page_title' => $pageTitle,
            'menu_title' => $menuTitle,
            'capability' => $capability,
            'callback' => $callback,
            'icon_url' => $iconUrl,
            'position' => $position,
        ];
        return $menuSlug;
    }
}

if (!function_exists('add_submenu_page')) {
    function add_submenu_page(string $parentSlug, string $pageTitle, string $menuTitle, string $capability, string $menuSlug, ?callable $callback = null, ?int $position = null): string
    {
        WordPressMockState::$submenuPages[$menuSlug] = [
            'parent_slug' => $parentSlug,
            'page_title' => $pageTitle,
            'menu_title' => $menuTitle,
            'capability' => $capability,
            'callback' => $callback,
            'position' => $position,
        ];
        return $menuSlug;
    }
}

if (!function_exists('add_meta_box')) {
    function add_meta_box(string $id, string $title, callable $callback, string|array|null $screen = null, string $context = 'advanced', string $priority = 'default', ?array $callback_args = null): void
    {
        WordPressMockState::$metaBoxes[$id] = [
            'title' => $title,
            'callback' => $callback,
            'screen' => $screen,
            'context' => $context,
            'priority' => $priority,
            'callback_args' => $callback_args,
        ];
    }
}

// Assets
if (!function_exists('wp_register_style')) {
    function wp_register_style(string $handle, string $src, array $deps = [], mixed $ver = false, string $media = 'all'): bool
    {
        WordPressMockState::$styles[$handle] = ['src' => $src, 'deps' => $deps, 'ver' => $ver, 'media' => $media];
        return true;
    }
}

if (!function_exists('wp_register_script')) {
    function wp_register_script(string $handle, string $src, array $deps = [], mixed $ver = false, bool $in_footer = false): bool
    {
        WordPressMockState::$scripts[$handle] = ['src' => $src, 'deps' => $deps, 'ver' => $ver, 'in_footer' => $in_footer];
        return true;
    }
}

if (!function_exists('wp_enqueue_style')) {
    function wp_enqueue_style(string $handle, string $src = '', array $deps = [], mixed $ver = false, string $media = 'all'): void
    {
        WordPressMockState::$enqueuedStyles[$handle] = true;
    }
}

if (!function_exists('wp_enqueue_script')) {
    function wp_enqueue_script(string $handle, string $src = '', array $deps = [], mixed $ver = false, bool $in_footer = false): void
    {
        WordPressMockState::$enqueuedScripts[$handle] = true;
    }
}

if (!function_exists('wp_enqueue_media')) {
    function wp_enqueue_media(array $args = []): void
    {
        WordPressMockState::$mediaEnqueued = true;
    }
}

// REST
if (!function_exists('register_post_meta')) {
    function register_post_meta(string $postType, string $metaKey, array $args = []): bool
    {
        WordPressMockState::$registeredMeta[$postType][$metaKey] = $args;
        return true;
    }
}

// Post & Media helpers
if (!function_exists('get_post')) {
    function get_post(mixed $post = null, string $output = 'OBJECT', string $filter = 'raw'): ?object
    {
        $id = is_object($post) ? ($post->ID ?? 0) : (int) $post;
        return WordPressMockState::$posts[$id] ?? null;
    }
}

if (!function_exists('get_post_type')) {
    function get_post_type(mixed $post = null): string|false
    {
        $obj = get_post($post);
        return $obj && isset($obj->post_type) ? $obj->post_type : false;
    }
}

if (!function_exists('get_the_ID')) {
    function get_the_ID(): int|false
    {
        return WordPressMockState::$currentPostId;
    }
}

if (!function_exists('get_post_mime_type')) {
    function get_post_mime_type(mixed $post = null): string|false
    {
        $obj = get_post($post);
        return $obj && isset($obj->post_mime_type) ? $obj->post_mime_type : false;
    }
}

if (!function_exists('get_attached_file')) {
    function get_attached_file(int $attachmentId, bool $unfiltered = false): string|false
    {
        $obj = get_post($attachmentId);
        return $obj && isset($obj->attached_file) ? $obj->attached_file : '/path/to/file.jpg';
    }
}

if (!function_exists('wp_get_attachment_url')) {
    function wp_get_attachment_url(int $attachmentId): string|false
    {
        $obj = get_post($attachmentId);
        return $obj && isset($obj->guid) ? $obj->guid : 'https://example.com/wp-content/uploads/file.jpg';
    }
}

if (!function_exists('wp_get_attachment_image_url')) {
    function wp_get_attachment_image_url(int $attachmentId, string|array $size = 'thumbnail', bool $icon = false): string|false
    {
        return wp_get_attachment_url($attachmentId);
    }
}

if (!function_exists('wp_get_attachment_metadata')) {
    function wp_get_attachment_metadata(int $attachmentId, bool $unfiltered = false): array|false
    {
        return WordPressMockState::$attachmentMetadata[$attachmentId] ?? ['width' => 800, 'height' => 600];
    }
}

if (!function_exists('wp_json_encode')) {
    function wp_json_encode(mixed $data, int $options = 0, int $depth = 512): string|false
    {
        return json_encode($data, $options, $depth);
    }
}

if (!function_exists('get_current_screen')) {
    function get_current_screen(): ?object
    {
        return WordPressMockState::$currentScreen;
    }
}

if (!function_exists('is_singular')) {
    function is_singular(mixed $post_types = ''): bool
    {
        return true;
    }
}

if (!function_exists('get_template_directory')) {
    function get_template_directory(): string
    {
        return '/path/to/theme';
    }
}

if (!function_exists('get_template_directory_uri')) {
    function get_template_directory_uri(): string
    {
        return 'https://example.com/wp-content/themes/mytheme';
    }
}

if (!function_exists('content_url')) {
    function content_url(string $path = ''): string
    {
        return 'https://example.com/wp-content' . ($path ? '/' . ltrim($path, '/') : '');
    }
}

if (!function_exists('__')) {
    function __(string $text, string $domain = 'default'): string
    {
        return $text;
    }
}

if (!function_exists('_e')) {
    function _e(string $text, string $domain = 'default'): void
    {
        echo $text;
    }
}

if (!function_exists('esc_attr_e')) {
    function esc_attr_e(string $text, string $domain = 'default'): void
    {
        echo esc_attr($text);
    }
}

if (!function_exists('esc_html__')) {
    function esc_html__(string $text, string $domain = 'default'): string
    {
        return esc_html($text);
    }
}

if (!function_exists('wp_die')) {
    function wp_die(string $message = '', string|int $title = '', string|array|int $args = []): void
    {
        throw new \RuntimeException($message);
    }
}

if (!function_exists('settings_fields')) {
    function settings_fields(string $optionGroup): void
    {
        echo '<input type="hidden" name="option_page" value="' . esc_attr($optionGroup) . '" />';
    }
}

if (!function_exists('do_settings_sections')) {
    function do_settings_sections(string $page): void
    {
        echo '<div class="settings-sections" data-page="' . esc_attr($page) . '"></div>';
    }
}

if (!function_exists('submit_button')) {
    function submit_button(string $text = 'Save Changes', string $type = 'primary', string $name = 'submit', bool $wrap = true, array|string|null $other_attributes = null): void
    {
        echo '<input type="submit" name="' . esc_attr($name) . '" value="' . esc_attr($text) . '" class="button button-primary" />';
    }
}
