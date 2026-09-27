<?php

declare(strict_types=1);

/**
 * Example Theme functions.php demonstrating the WordPress Theme Framework.
 */

// If installed via Composer inside the theme:
if (file_exists(get_template_directory() . '/vendor/autoload.php')) {
    require_once get_template_directory() . '/vendor/autoload.php';
}

use KulangaraAlwinJoy\ThemeFramework\Assets;
use KulangaraAlwinJoy\ThemeFramework\Field;
use KulangaraAlwinJoy\ThemeFramework\Framework;
use KulangaraAlwinJoy\ThemeFramework\MenuField;
use KulangaraAlwinJoy\ThemeFramework\Option;
use KulangaraAlwinJoy\ThemeFramework\OptionPage;
use KulangaraAlwinJoy\ThemeFramework\PostType;

// 1. Initialize the framework
Framework::boot();

// 2. Register Custom Post Type: Project
PostType::register('project', [
    'label' => 'Projects',
    'singular_label' => 'Project',
    'supports' => [
        'title',
        'editor',
        'thumbnail',
    ],
    'public' => true,
    'show_ui' => true,
    'show_in_rest' => true,
    'has_archive' => true,
    'rewrite' => [
        'slug' => 'projects',
    ],
    'menu_icon' => 'dashicons-portfolio',
]);

// 3. Register Post Fields for Project
Field::add('location', [
    'label'        => 'Location',
    'type'         => 'text',
    'post_type'    => 'project',
    'description'  => 'City and Country where the project was completed.',
    'show_in_rest' => true,
    'validation'   => [
        'required'   => true,
        'min_length' => 2,
    ],
]);

Field::add('icon', [
    'label'         => 'Icon',
    'type'          => 'media',
    'post_type'     => 'project',
    'description'   => 'Project icon (PNG or SVG).',
    'allowed_types' => [
        'image/png',
        'image/svg+xml',
    ],
    'show_in_rest'  => true,
]);

Field::add('gallery', [
    'label'        => 'Gallery',
    'type'         => 'gallery',
    'post_type'    => 'project',
    'description'  => 'Add and reorder high-resolution project photos.',
    'show_in_rest' => true,
]);

// Optional: Conditional field demo
Field::add('project_type', [
    'label'     => 'Project Type',
    'type'      => 'select',
    'post_type' => 'project',
    'choices'   => [
        'standard' => 'Standard Case Study',
        'video'    => 'Video Showcase',
    ],
    'default'   => 'standard',
]);

Field::add('video_url', [
    'label'      => 'Video URL',
    'type'       => 'url',
    'post_type'  => 'project',
    'conditions' => [
        [
            'field'   => 'project_type',
            'compare' => '==',
            'value'   => 'video',
        ],
    ],
]);

// 4. Register Menu Item Custom Fields
MenuField::add('is_mega_menu', [
    'label'          => 'Mega Menu',
    'type'           => 'checkbox',
    'checkbox_label' => 'Enable full-width mega menu dropdown for this item',
]);

MenuField::add('menu_icon', [
    'label'         => 'Menu Icon',
    'type'          => 'media',
    'description'   => 'Optional navigation icon',
    'allowed_types' => [
        'image/png',
        'image/svg+xml',
    ],
]);

// 5. Register Option Pages
OptionPage::register('theme_settings', [
    'title'      => 'Theme Settings',
    'menu_title' => 'Theme Settings',
    'capability' => 'manage_options',
    'icon'       => 'dashicons-admin-generic',
    'position'   => 60,
]);

// 6. Register Theme Options
Option::add('logo', [
    'label' => 'Site Logo',
    'type'  => 'media',
    'page'  => 'theme_settings',
]);

Option::add('contact_phone', [
    'label' => 'Contact Phone',
    'type'  => 'text',
    'page'  => 'theme_settings',
]);

Option::add('custom_css', [
    'label'         => 'Custom CSS',
    'type'          => 'textarea',
    'page'          => 'theme_settings',
    'sanitize_mode' => 'css',
    'description'   => 'Custom CSS injected into the frontend <head>.',
]);

Option::add('custom_js', [
    'label'         => 'Custom JavaScript',
    'type'          => 'textarea',
    'page'          => 'theme_settings',
    'sanitize_mode' => 'js',
    'description'   => 'Custom tracking or script tag (requires unfiltered_html capability).',
]);

// 7. Inject Custom CSS / JS on Frontend
add_action('wp_head', function () {
    $customCss = (string) Option::get('custom_css', '');
    if ($customCss !== '') {
        echo '<style id="theme-custom-css">' . wp_strip_all_tags($customCss) . '</style>' . "\n";
    }
});

add_action('wp_footer', function () {
    $customJs = (string) Option::get('custom_js', '');
    if ($customJs !== '') {
        // Output JavaScript directly for authorized administrators
        echo '<script id="theme-custom-js">' . $customJs . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
});
