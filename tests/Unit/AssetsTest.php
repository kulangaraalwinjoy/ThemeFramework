<?php

declare(strict_types=1);

namespace YourVendor\ThemeFramework\Tests\Unit;

use YourVendor\ThemeFramework\Assets\AssetManager;
use YourVendor\ThemeFramework\Assets\Assets;
use YourVendor\ThemeFramework\Field\Field;
use YourVendor\ThemeFramework\Tests\TestCase;
use YourVendor\ThemeFramework\Tests\WordPressMockState;

class AssetsTest extends TestCase
{
    public function testAssetsRegistration(): void
    {
        Assets::registerStyle('my-style', 'https://example.com/style.css', [], '1.0');
        Assets::registerScript('my-script', 'https://example.com/script.js', [], '1.0');

        $this->assertArrayHasKey('my-style', WordPressMockState::$styles);
        $this->assertArrayHasKey('my-script', WordPressMockState::$scripts);
    }

    public function testMediaAssetNotEnqueuedOnUnrelatedScreen(): void
    {
        // Screen with only text field
        Field::add('simple_text', [
            'label' => 'Text',
            'type' => 'text',
            'post_type' => 'post',
        ]);

        WordPressMockState::$currentScreen = (object) [
            'base' => 'post',
            'post_type' => 'post',
            'id' => 'post',
        ];

        AssetManager::handleAdminAssets('post.php');

        $this->assertArrayHasKey('theme-framework-admin', WordPressMockState::$enqueuedStyles);
        $this->assertFalse(WordPressMockState::$mediaEnqueued, 'Media library must not be enqueued when no media/gallery fields exist.');
    }

    public function testMediaAssetEnqueuedWhenMediaFieldPresent(): void
    {
        Field::add('project_logo', [
            'label' => 'Logo',
            'type' => 'media',
            'post_type' => 'project',
        ]);

        WordPressMockState::$currentScreen = (object) [
            'base' => 'post',
            'post_type' => 'project',
            'id' => 'project',
        ];

        AssetManager::handleAdminAssets('post.php');

        $this->assertTrue(WordPressMockState::$mediaEnqueued, 'Media library must be enqueued when media field exists.');
        $this->assertArrayHasKey('theme-framework-media', WordPressMockState::$enqueuedStyles);
        $this->assertArrayHasKey('theme-framework-media', WordPressMockState::$enqueuedScripts);
    }
}
