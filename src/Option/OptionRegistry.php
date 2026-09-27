<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Option;

use InvalidArgumentException;
use KulangaraAlwinJoy\ThemeFramework\Field\FieldType;

/**
 * Registry for Option Pages and Options.
 */
class OptionRegistry
{
    /**
     * Registered option pages.
     *
     * @var array<string, array<string, mixed>>
     */
    protected static array $pages = [];

    /**
     * Registered options.
     *
     * @var array<string, array<string, mixed>>
     */
    protected static array $options = [];

    /**
     * Register an Option Page.
     *
     * @param string $id
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     */
    public static function registerPage(string $id, array $config): array
    {
        $id = trim($id);
        if ($id === '') {
            throw new InvalidArgumentException('Option page ID cannot be empty.');
        }

        if (empty($config['title'])) {
            throw new InvalidArgumentException(sprintf('Option page "%s" is missing a title.', $id));
        }

        $page = array_merge([
            'id' => $id,
            'title' => '',
            'menu_title' => $config['title'],
            'capability' => 'manage_options',
            'icon' => 'dashicons-admin-generic',
            'position' => 60,
            'parent' => null, // null for top-level menu, or parent slug for submenu
            'sections' => [
                'default' => [
                    'id' => 'default',
                    'title' => '',
                ],
            ],
        ], $config);

        $page['id'] = $id;

        /**
         * Filter registered option page configuration.
         *
         * @param array $page
         */
        $page = (array) apply_filters('theme_framework_option_page_definition', $page);

        self::$pages[$id] = $page;
        return $page;
    }

    /**
     * Register an Option.
     *
     * @param string $id
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     */
    public static function addOption(string $id, array $config): array
    {
        $id = trim($id);
        if ($id === '') {
            throw new InvalidArgumentException('Option ID cannot be empty.');
        }

        if (empty($config['label'])) {
            throw new InvalidArgumentException(sprintf('Option "%s" is missing a label.', $id));
        }

        $type = (string) ($config['type'] ?? 'text');
        if (!FieldType::has($type)) {
            throw new InvalidArgumentException(sprintf('Unknown field type: "%s".', $type));
        }

        $page = (string) ($config['page'] ?? 'theme_settings');
        $allowedTypes = $config['allowed_types'] ?? $config['library'] ?? [];

        $option = array_merge([
            'id' => $id,
            'label' => '',
            'type' => 'text',
            'page' => $page,
            'section' => 'default',
            'description' => '',
            'context' => 'option',
            'validation' => [],
            'conditions' => [],
            'default' => null,
            'allowed_types' => (array) $allowedTypes,
            'library' => (array) $allowedTypes,
            'attributes' => [],
        ], $config);

        $option['id'] = $id;
        $option['page'] = $page;

        /**
         * Filter option definition.
         *
         * @param array $option
         */
        $option = (array) apply_filters('theme_framework_option_definition', $option);

        self::$options[$id] = $option;
        return $option;
    }

    /**
     * Retrieve all registered pages.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function getPages(): array
    {
        return self::$pages;
    }

    /**
     * Retrieve a specific page definition.
     *
     * @param string $id
     * @return array<string, mixed>|null
     */
    public static function getPage(string $id): ?array
    {
        return self::$pages[$id] ?? null;
    }

    /**
     * Retrieve all registered options, optionally filtered by page.
     *
     * @param string|null $page
     * @return array<string, array<string, mixed>>
     */
    public static function getOptions(?string $page = null): array
    {
        if ($page === null) {
            return self::$options;
        }

        $filtered = [];
        foreach (self::$options as $id => $opt) {
            if (($opt['page'] ?? '') === $page) {
                $filtered[$id] = $opt;
            }
        }

        return $filtered;
    }

    /**
     * Retrieve an option definition.
     *
     * @param string $id
     * @return array<string, mixed>|null
     */
    public static function getOption(string $id): ?array
    {
        return self::$options[$id] ?? null;
    }

    /**
     * Reset registry.
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$pages = [];
        self::$options = [];
    }
}
