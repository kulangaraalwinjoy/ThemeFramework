<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Tests\Integration;

use KulangaraAlwinJoy\ThemeFramework\Assets\AssetManager;
use KulangaraAlwinJoy\ThemeFramework\Field\Field;
use KulangaraAlwinJoy\ThemeFramework\Field\FieldSaver;
use KulangaraAlwinJoy\ThemeFramework\Framework;
use KulangaraAlwinJoy\ThemeFramework\Menu\MenuField;
use KulangaraAlwinJoy\ThemeFramework\Menu\MenuFieldSaver;
use KulangaraAlwinJoy\ThemeFramework\Option\Option;
use KulangaraAlwinJoy\ThemeFramework\Option\OptionPage;
use KulangaraAlwinJoy\ThemeFramework\PostType\PostType;
use KulangaraAlwinJoy\ThemeFramework\Security\Nonce;
use KulangaraAlwinJoy\ThemeFramework\Tests\TestCase;
use KulangaraAlwinJoy\ThemeFramework\Tests\WordPressMockState;

/**
 * Realistic End-to-End WordPress Integration Test.
 *
 * Verifies all 12 requirements outlined in Section 45:
 * 1. Post Type registration
 * 2. Text field
 * 3. Media field
 * 4. Gallery field
 * 5. Menu checkbox
 * 6. Menu media field
 * 7. Option page
 * 8. Option field
 * 9. Saving/loading/deleting values
 * 10. Security checks
 * 11. Asset loading
 * 12. Composer autoloading
 */
class WordPressIntegrationTest extends TestCase
{
    public function testRealisticEndToEndThemeConfiguration(): void
    {
        // 12. Composer Autoloading check
        $this->assertTrue(class_exists(Framework::class));
        $this->assertTrue(class_exists(PostType::class));
        $this->assertTrue(class_exists(Field::class));
        $this->assertTrue(class_exists(MenuField::class));
        $this->assertTrue(class_exists(Option::class));
        $this->assertTrue(class_exists(OptionPage::class));

        // 1. Post Type registration
        PostType::register('project', [
            'label' => 'Projects',
            'supports' => ['title', 'editor', 'thumbnail'],
            'public' => true,
            'show_ui' => true,
            'show_in_rest' => true,
            'has_archive' => true,
            'rewrite' => ['slug' => 'projects'],
            'menu_icon' => 'dashicons-portfolio',
        ]);

        PostType::registerAll();
        $this->assertArrayHasKey('project', WordPressMockState::$postTypes);
        $this->assertSame('Projects', WordPressMockState::$postTypes['project']['labels']['name']);

        // 2. Text field
        Field::add('location', [
            'label'     => 'Location',
            'type'      => 'text',
            'post_type' => 'project',
            'validation' => [
                'required' => true,
                'min_length' => 2,
            ],
        ]);

        // 3. Media field
        Field::add('icon', [
            'label'     => 'Icon',
            'type'      => 'media',
            'post_type' => 'project',
            'allowed_types' => ['image/png', 'image/svg+xml'],
        ]);

        // 4. Gallery field
        Field::add('gallery', [
            'label'     => 'Gallery',
            'type'      => 'gallery',
            'post_type' => 'project',
        ]);

        // 5. Menu checkbox
        MenuField::add('is_mega_menu', [
            'label' => 'Mega Menu',
            'type'  => 'checkbox',
        ]);

        // 6. Menu media field
        MenuField::add('menu_icon', [
            'label' => 'Menu Icon',
            'type'  => 'media',
            'allowed_types' => ['image/png', 'image/svg+xml'],
        ]);

        // 7. Option page
        OptionPage::register('theme_settings', [
            'title'      => 'Theme Settings',
            'menu_title' => 'Theme Settings',
            'capability' => 'manage_options',
            'icon'       => 'dashicons-admin-generic',
            'position'   => 60,
        ]);
        OptionPage::registerAdminMenus();
        $this->assertArrayHasKey('theme_settings', WordPressMockState::$menuPages);

        // 8. Option field
        Option::add('custom_css', [
            'label' => 'Custom CSS',
            'type'  => 'textarea',
            'page'  => 'theme_settings',
        ]);
        OptionPage::registerSettings();
        $this->assertArrayHasKey('theme_settings', WordPressMockState::$registeredSettings);
        $this->assertArrayHasKey('_theme_option_custom_css', WordPressMockState::$registeredSettings['theme_settings']);

        // Set up mock post and media attachments
        $projectId = 101;
        WordPressMockState::$posts[$projectId] = (object) [
            'ID' => $projectId,
            'post_type' => 'project',
            'post_title' => 'Flagship Headquarters',
        ];

        $iconAttachmentId = 501;
        WordPressMockState::$posts[$iconAttachmentId] = (object) [
            'ID' => $iconAttachmentId,
            'post_type' => 'attachment',
            'post_mime_type' => 'image/png',
            'post_title' => 'Project Icon',
            'guid' => 'https://example.com/icon.png',
        ];

        $galleryImg1 = 601;
        $galleryImg2 = 602;
        WordPressMockState::$posts[$galleryImg1] = (object) [
            'ID' => $galleryImg1,
            'post_type' => 'attachment',
            'post_mime_type' => 'image/jpeg',
            'post_title' => 'Gallery 1',
            'guid' => 'https://example.com/gal1.jpg',
        ];
        WordPressMockState::$posts[$galleryImg2] = (object) [
            'ID' => $galleryImg2,
            'post_type' => 'attachment',
            'post_mime_type' => 'image/jpeg',
            'post_title' => 'Gallery 2',
            'guid' => 'https://example.com/gal2.jpg',
        ];

        // 9. Saving / Loading / Deleting values
        // Post fields save
        $fieldSaver = new FieldSaver();
        $postData = [
            '_theme_framework_nonce' => Nonce::create('post_fields_' . $projectId),
            'location' => 'San Francisco, CA',
            'icon' => (string) $iconAttachmentId,
            'gallery' => $galleryImg1 . ',' . $galleryImg2,
        ];

        $fields = [
            'location' => \KulangaraAlwinJoy\ThemeFramework\Field\FieldRegistry::get('location'),
            'icon' => \KulangaraAlwinJoy\ThemeFramework\Field\FieldRegistry::get('icon'),
            'gallery' => \KulangaraAlwinJoy\ThemeFramework\Field\FieldRegistry::get('gallery'),
        ];

        $saved = $fieldSaver->save(
            $projectId,
            $fields,
            Field::getStorage(),
            $postData,
            'post_fields_' . $projectId
        );
        $this->assertTrue($saved);

        // Verify post field values
        $this->assertSame('San Francisco, CA', Field::get('location', $projectId));
        $this->assertSame($iconAttachmentId, Field::get('icon', $projectId));
        $this->assertSame('https://example.com/icon.png', Field::url('icon', $projectId));

        $images = Field::images('gallery', $projectId);
        $this->assertCount(2, $images);
        $this->assertSame('https://example.com/gal1.jpg', $images[0]);
        $this->assertSame('https://example.com/gal2.jpg', $images[1]);

        // Menu fields save
        $menuItemId = 205;
        $menuSaver = new MenuFieldSaver();
        $menuPostData = [
            '_theme_menu_nonce' => Nonce::create('menu_fields'),
            '_theme_menu' => [
                $menuItemId => [
                    'is_mega_menu' => '1',
                    'menu_icon' => (string) $iconAttachmentId,
                ],
            ],
        ];

        $menuSaved = $menuSaver->save(1, $menuItemId, $menuPostData);
        $this->assertTrue($menuSaved);
        $this->assertSame(1, MenuField::get('is_mega_menu', $menuItemId));
        $this->assertSame($iconAttachmentId, MenuField::get('menu_icon', $menuItemId));
        $this->assertSame('https://example.com/icon.png', MenuField::url('menu_icon', $menuItemId));

        // Option field save & get
        Option::set('custom_css', '.header { background: #000; }');
        $this->assertSame('.header { background: #000; }', Option::get('custom_css'));

        // Deletion
        Field::delete('location', $projectId);
        $this->assertNull(Field::get('location', $projectId));

        MenuField::delete('is_mega_menu', $menuItemId);
        $this->assertNull(MenuField::get('is_mega_menu', $menuItemId));

        Option::delete('custom_css');
        $this->assertNull(Option::get('custom_css'));

        // 10. Security Checks
        // Failure on invalid nonce
        $badNonceData = $postData;
        $badNonceData['_theme_framework_nonce'] = 'forged_token';
        $badSave = $fieldSaver->save(
            $projectId,
            $fields,
            Field::getStorage(),
            $badNonceData,
            'post_fields_' . $projectId
        );
        $this->assertFalse($badSave, 'Save must fail when nonce verification fails.');

        // Failure on unauthorized user
        WordPressMockState::$capabilities['edit_post'] = false;
        $unauthorizedSave = $fieldSaver->save(
            $projectId,
            $fields,
            Field::getStorage(),
            $postData,
            'post_fields_' . $projectId
        );
        $this->assertFalse($unauthorizedSave, 'Save must fail when user lacks capability.');
        WordPressMockState::$capabilities['edit_post'] = true;

        // 11. Asset Loading
        // On project post edit screen, media assets must be enqueued
        WordPressMockState::$currentScreen = (object) [
            'base' => 'post',
            'post_type' => 'project',
            'id' => 'project',
        ];

        AssetManager::handleAdminAssets('post.php');
        $this->assertTrue(WordPressMockState::$mediaEnqueued);
        $this->assertArrayHasKey('theme-framework-media', WordPressMockState::$enqueuedStyles);
        $this->assertArrayHasKey('theme-framework-gallery', WordPressMockState::$enqueuedStyles);
    }
}
