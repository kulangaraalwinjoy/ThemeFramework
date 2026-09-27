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
}
