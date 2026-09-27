<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Tests\Unit;

use KulangaraAlwinJoy\ThemeFramework\Storage\MenuMetaStorage;
use KulangaraAlwinJoy\ThemeFramework\Storage\OptionStorage;
use KulangaraAlwinJoy\ThemeFramework\Storage\PostMetaStorage;
use KulangaraAlwinJoy\ThemeFramework\Tests\TestCase;
use KulangaraAlwinJoy\ThemeFramework\Tests\WordPressMockState;

class StorageTest extends TestCase
{
    public function testPostMetaStorage(): void
    {
        $storage = new PostMetaStorage('_custom_');
        $postId = 5;

        $this->assertTrue($storage->set('city', 'Berlin', $postId));
        $this->assertSame('Berlin', $storage->get('city', $postId));
        $this->assertSame('Berlin', WordPressMockState::$postMeta[5]['_custom_city']);

        $this->assertTrue($storage->delete('city', $postId));
        $this->assertNull($storage->get('city', $postId));
    }

    public function testMenuMetaStorage(): void
    {
        $storage = new MenuMetaStorage();
        $menuItemId = 22;

        $this->assertTrue($storage->set('badge', 'New', $menuItemId));
        $this->assertSame('New', $storage->get('badge', $menuItemId));
        $this->assertSame('New', WordPressMockState::$postMeta[22]['_theme_menu_badge']);

        $this->assertTrue($storage->delete('badge', $menuItemId));
        $this->assertNull($storage->get('badge', $menuItemId));
    }

    public function testOptionStorage(): void
    {
        $storage = new OptionStorage();

        $this->assertTrue($storage->set('site_color', '#333333'));
        $this->assertSame('#333333', $storage->get('site_color'));
        $this->assertSame('#333333', WordPressMockState::$options['_theme_option_site_color']);

        $this->assertTrue($storage->delete('site_color'));
        $this->assertNull($storage->get('site_color'));
    }

    public function testPostMetaStorageUnprefixedFallback(): void
    {
        $storage = new PostMetaStorage('_theme_');
        $postId = 88;

        // Set unprefixed meta directly
        WordPressMockState::$postMeta[88]['legacy_field'] = 'LegacyValue';

        // When prefixed meta does not exist, it should fallback to unprefixed
        $this->assertSame('LegacyValue', $storage->get('legacy_field', $postId));

        // When prefixed meta exists, it should take precedence
        $storage->set('legacy_field', 'NewPrefixedValue', $postId);
        $this->assertSame('NewPrefixedValue', $storage->get('legacy_field', $postId));
    }

    public function testMenuMetaStorageUnprefixedFallback(): void
    {
        $storage = new MenuMetaStorage('_theme_menu_');
        $menuItemId = 99;

        // Set unprefixed meta directly
        WordPressMockState::$postMeta[99]['menu_legacy'] = 'LegacyMenuValue';

        // When prefixed meta does not exist, it should fallback to unprefixed
        $this->assertSame('LegacyMenuValue', $storage->get('menu_legacy', $menuItemId));

        // When prefixed meta exists, it should take precedence
        $storage->set('menu_legacy', 'NewPrefixedMenuValue', $menuItemId);
        $this->assertSame('NewPrefixedMenuValue', $storage->get('menu_legacy', $menuItemId));
    }
}
