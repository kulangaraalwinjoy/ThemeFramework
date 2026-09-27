<?php

declare(strict_types=1);

namespace YourVendor\ThemeFramework\Tests\Unit;

use YourVendor\ThemeFramework\Field\Field;
use YourVendor\ThemeFramework\REST\RestSupport;
use YourVendor\ThemeFramework\Tests\TestCase;
use YourVendor\ThemeFramework\Tests\WordPressMockState;

class RestSupportTest extends TestCase
{
    public function testRestMetaRegistration(): void
    {
        Field::add('location', [
            'label' => 'Location',
            'type' => 'text',
            'post_type' => 'project',
            'show_in_rest' => true,
        ]);

        Field::add('icon', [
            'label' => 'Icon',
            'type' => 'media',
            'post_type' => 'project',
            'show_in_rest' => true,
        ]);

        RestSupport::registerRestFields();

        $this->assertArrayHasKey('project', WordPressMockState::$registeredMeta);
        $projectMeta = WordPressMockState::$registeredMeta['project'];

        $this->assertArrayHasKey('_theme_location', $projectMeta);
        $this->assertSame('string', $projectMeta['_theme_location']['type']);
        $this->assertTrue($projectMeta['_theme_location']['single']);

        $this->assertArrayHasKey('_theme_icon', $projectMeta);
        $this->assertSame('integer', $projectMeta['_theme_icon']['type']);

        // Test auth callback
        $authCallback = $projectMeta['_theme_location']['auth_callback'];
        WordPressMockState::$capabilities['edit_post'] = true;
        $this->assertTrue($authCallback(true, '_theme_location', 10));

        WordPressMockState::$capabilities['edit_post'] = false;
        $this->assertFalse($authCallback(true, '_theme_location', 10));
    }
}
