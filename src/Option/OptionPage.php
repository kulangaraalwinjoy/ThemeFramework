<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Option;

use KulangaraAlwinJoy\ThemeFramework\Field\FieldRenderer;
use KulangaraAlwinJoy\ThemeFramework\Field\FieldType;
use KulangaraAlwinJoy\ThemeFramework\Field\FieldValidator;
use KulangaraAlwinJoy\ThemeFramework\Security\Capability;
use KulangaraAlwinJoy\ThemeFramework\Storage\OptionStorage;

/**
 * Handles WordPress Settings API integration and option page rendering.
 */
class OptionPage
{
    /**
     * Register a new option page.
     *
     * @param string $id Unique page slug.
     * @param array<string, mixed> $config Configuration array.
     * @return void
     */
    public static function register(string $id, array $config): void
    {
        OptionRegistry::registerPage($id, $config);
    }

    /**
     * Admin menu registration hook callback.
     *
     * @return void
     */
    public static function registerAdminMenus(): void
    {
        $pages = OptionRegistry::getPages();

        foreach ($pages as $pageId => $page) {
            $title = (string) ($page['title'] ?? 'Settings');
            $menuTitle = (string) ($page['menu_title'] ?? $title);
            $capability = (string) ($page['capability'] ?? 'manage_options');
            $icon = (string) ($page['icon'] ?? 'dashicons-admin-generic');
            $position = isset($page['position']) ? (int) $page['position'] : 60;
            $parent = $page['parent'] ?? null;

            $renderCallback = function () use ($pageId) {
                self::renderPage($pageId);
            };

            if (!empty($parent)) {
                add_submenu_page(
                    (string) $parent,
                    $title,
                    $menuTitle,
                    $capability,
                    $pageId,
                    $renderCallback,
                    $position
                );
            } else {
                add_menu_page(
                    $title,
                    $menuTitle,
                    $capability,
                    $pageId,
                    $renderCallback,
                    $icon,
                    $position
                );
            }
        }
    }

    /**
     * Register settings and sections with WordPress Settings API.
     *
     * @return void
     */
    public static function registerSettings(): void
    {
        $storage = Option::getStorage();
        $pages = OptionRegistry::getPages();

        foreach ($pages as $pageId => $page) {
            $options = OptionRegistry::getOptions($pageId);
            $capability = (string) ($page['capability'] ?? 'manage_options');

            // Collect sections
            $sections = (array) ($page['sections'] ?? ['default' => ['id' => 'default', 'title' => '']]);

            foreach ($sections as $sectionId => $sectionData) {
                $sectionCallback = !empty($sectionData['callback']) && is_callable($sectionData['callback'])
                    ? $sectionData['callback']
                    : '__return_null';

                add_settings_section(
                    $sectionId,
                    (string) ($sectionData['title'] ?? ''),
                    $sectionCallback,
                    $pageId
                );
            }

            // Register each option in Settings API
            foreach ($options as $optionId => $option) {
                $rawOptionName = $storage->getOptionName($optionId);
                $sectionId = (string) ($option['section'] ?? 'default');
                $type = (string) ($option['type'] ?? 'text');

                register_setting(
                    $pageId,
                    $rawOptionName,
                    [
                        'type' => self::mapOptionType($type),
                        'description' => (string) ($option['description'] ?? ''),
                        'sanitize_callback' => function ($value) use ($option, $type) {
                            $validator = new FieldValidator();
                            if (!$validator->validate($value, $option)) {
                                return Option::get($option['id']);
                            }

                            if (!empty($option['sanitize']) && is_callable($option['sanitize'])) {
                                return call_user_func($option['sanitize'], $value, $option);
                            }

                            $handler = FieldType::get($type);
                            return $handler->sanitize($value, $option);
                        },
                        'show_in_rest' => !empty($option['show_in_rest']),
                        'default' => $option['default'] ?? null,
                    ]
                );

                add_settings_field(
                    $optionId,
                    (string) ($option['label'] ?? ''),
                    function () use ($option, $rawOptionName) {
                        self::renderOptionField($option, $rawOptionName);
                    },
                    $pageId,
                    $sectionId,
                    ['label_for' => $optionId]
                );
            }
        }
    }

    /**
     * Map field types to Settings API types.
     *
     * @param string $type
     * @return string
     */
    protected static function mapOptionType(string $type): string
    {
        return match ($type) {
            'number' => 'number',
            'checkbox' => 'boolean',
            'gallery' => 'array',
            default => 'string',
        };
    }

    /**
     * Render an option field control inside Settings API form.
     *
     * @param array<string, mixed> $option
     * @param string $rawOptionName
     * @return void
     */
    protected static function renderOptionField(array $option, string $rawOptionName): void
    {
        $renderer = new FieldRenderer();
        $optionId = (string) $option['id'];
        $value = Option::get($optionId, $option['default'] ?? null);

        $renderer->renderControl($option, $value, $rawOptionName, $optionId);

        if (!empty($option['description'])) {
            printf(
                '<p class="description">%s</p>',
                esc_html((string) $option['description'])
            );
        }
    }

    /**
     * Render the admin page markup.
     *
     * @param string $pageId
     * @return void
     */
    public static function renderPage(string $pageId): void
    {
        $page = OptionRegistry::getPage($pageId);
        if (!$page) {
            wp_die(esc_html__('Invalid option page.', 'wp-theme-framework'));
        }

        $capability = (string) ($page['capability'] ?? 'manage_options');
        if (!Capability::can($capability)) {
            wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'wp-theme-framework'));
        }

        $title = (string) ($page['title'] ?? 'Settings');

        ?>
        <div class="wrap theme-framework-settings-wrap">
            <h1><?php echo esc_html($title); ?></h1>

            <form method="post" action="options.php">
                <?php
                settings_fields($pageId);
                do_settings_sections($pageId);
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}
