<?php

declare(strict_types=1);

namespace YourVendor\ThemeFramework\Tests\Unit;

use YourVendor\ThemeFramework\Menu\MenuField;
use YourVendor\ThemeFramework\Menu\MenuFieldRegistry;
use YourVendor\ThemeFramework\Menu\MenuFieldSaver;
use YourVendor\ThemeFramework\Security\Nonce;
use YourVendor\ThemeFramework\Tests\TestCase;
use YourVendor\ThemeFramework\Tests\WordPressMockState;

class MenuFieldTest extends TestCase
{
    public function testMenuFieldRegistrationAndGetSet(): void
    {
        MenuField::add('is_mega_menu', [
            'label' => 'Mega Menu',
            'type' => 'checkbox',
        ]);

        MenuField::add('menu_icon', [
            'label' => 'Menu Icon',
            'type' => 'media',
            'allowed_types' => ['image/png'],
        ]);

        $this->assertTrue(MenuFieldRegistry::has('is_mega_menu'));
        $this->assertTrue(MenuFieldRegistry::has('menu_icon'));

        $menuItemId = 45;
        MenuField::set('is_mega_menu', $menuItemId, 1);
        $this->assertSame(1, MenuField::get('is_mega_menu', $menuItemId));

        // Storage verification
        $this->assertSame(1, WordPressMockState::$postMeta[45]['_theme_menu_is_mega_menu']);

        MenuField::delete('is_mega_menu', $menuItemId);
        $this->assertNull(MenuField::get('is_mega_menu', $menuItemId));
    }

    public function testMenuFieldSaveViaSaver(): void
    {
        MenuField::add('is_mega_menu', [
            'label' => 'Mega Menu',
            'type' => 'checkbox',
        ]);

        $saver = new MenuFieldSaver();
        $menuId = 12;
        $menuItemId = 77;

        $postData = [
            '_theme_menu_nonce' => Nonce::create('menu_fields'),
            '_theme_menu' => [
                77 => [
                    'is_mega_menu' => '1',
                ],
            ],
        ];

        $saved = $saver->save($menuId, $menuItemId, $postData);
        $this->assertTrue($saved);
        $this->assertSame(1, MenuField::get('is_mega_menu', 77));
    }

    public function testMenuFieldSaveFailsOnInvalidNonce(): void
    {
        MenuField::add('is_mega_menu', [
            'label' => 'Mega Menu',
            'type' => 'checkbox',
        ]);

        $saver = new MenuFieldSaver();
        $postData = [
            '_theme_menu_nonce' => 'invalid-nonce',
            '_theme_menu' => [
                77 => ['is_mega_menu' => '1'],
            ],
        ];

        $saved = $saver->save(12, 77, $postData);
        $this->assertFalse($saved);
        $this->assertNull(MenuField::get('is_mega_menu', 77));
    }
}
