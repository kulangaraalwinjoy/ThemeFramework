<?php

declare(strict_types=1);

namespace YourVendor\ThemeFramework\Tests\Unit;

use YourVendor\ThemeFramework\Field\FieldType;
use YourVendor\ThemeFramework\Framework;
use YourVendor\ThemeFramework\Tests\TestCase;
use YourVendor\ThemeFramework\Tests\WordPressMockState;

class FrameworkTest extends TestCase
{
    public function testBootIsIdempotent(): void
    {
        $this->assertTrue(Framework::isBooted());

        $initialActionsCount = count(WordPressMockState::$actions);

        // Call boot second time
        Framework::boot();
        $this->assertTrue(Framework::isBooted());

        // Should not duplicate hook registrations
        $this->assertSame($initialActionsCount, count(WordPressMockState::$actions));
    }

    public function testRegistersDefaultFieldTypes(): void
    {
        $expectedTypes = [
            'text',
            'textarea',
            'number',
            'url',
            'email',
            'checkbox',
            'select',
            'media',
            'gallery',
            'custom',
        ];

        foreach ($expectedTypes as $type) {
            $this->assertTrue(FieldType::has($type), sprintf('Field type "%s" was not registered on boot.', $type));
        }
    }

    public function testRegistersCoreWordPressHooks(): void
    {
        $this->assertArrayHasKey('init', WordPressMockState::$actions);
        $this->assertArrayHasKey('admin_init', WordPressMockState::$actions);
        $this->assertArrayHasKey('admin_menu', WordPressMockState::$actions);
        $this->assertArrayHasKey('add_meta_boxes', WordPressMockState::$actions);
        $this->assertArrayHasKey('save_post', WordPressMockState::$actions);
        $this->assertArrayHasKey('wp_nav_menu_item_custom_fields', WordPressMockState::$actions);
        $this->assertArrayHasKey('wp_update_nav_menu_item', WordPressMockState::$actions);
        $this->assertArrayHasKey('admin_enqueue_scripts', WordPressMockState::$actions);
        $this->assertArrayHasKey('wp_enqueue_scripts', WordPressMockState::$actions);
    }
}
