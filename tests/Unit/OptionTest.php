<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Tests\Unit;

use KulangaraAlwinJoy\ThemeFramework\Option\Option;
use KulangaraAlwinJoy\ThemeFramework\Option\OptionPage;
use KulangaraAlwinJoy\ThemeFramework\Option\OptionRegistry;
use KulangaraAlwinJoy\ThemeFramework\Tests\TestCase;
use KulangaraAlwinJoy\ThemeFramework\Tests\WordPressMockState;

class OptionTest extends TestCase
{
    public function testOptionPageAndOptionRegistration(): void
    {
        OptionPage::register('theme_settings', [
            'title' => 'Theme Settings',
            'menu_title' => 'Theme Settings',
            'capability' => 'manage_options',
            'icon' => 'dashicons-admin-generic',
            'position' => 60,
        ]);

        Option::add('custom_css', [
            'label' => 'Custom CSS',
            'type' => 'textarea',
            'page' => 'theme_settings',
        ]);

        Option::add('logo', [
            'label' => 'Logo',
            'type' => 'media',
            'page' => 'theme_settings',
        ]);

        $this->assertNotNull(OptionRegistry::getPage('theme_settings'));
        $this->assertNotNull(OptionRegistry::getOption('custom_css'));
        $this->assertNotNull(OptionRegistry::getOption('logo'));

        // Admin menu registration
        OptionPage::registerAdminMenus();
        $this->assertArrayHasKey('theme_settings', WordPressMockState::$menuPages);
        $this->assertSame('Theme Settings', WordPressMockState::$menuPages['theme_settings']['page_title']);

        // Settings API registration
        OptionPage::registerSettings();
        $this->assertArrayHasKey('theme_settings', WordPressMockState::$registeredSettings);
        $this->assertArrayHasKey('_theme_option_custom_css', WordPressMockState::$registeredSettings['theme_settings']);
        $this->assertArrayHasKey('_theme_option_logo', WordPressMockState::$registeredSettings['theme_settings']);
    }

    public function testOptionGetSetDelete(): void
    {
        Option::add('phone_number', [
            'label' => 'Phone',
            'type' => 'text',
        ]);

        Option::set('phone_number', '+1-555-0199');
        $this->assertSame('+1-555-0199', Option::get('phone_number'));
        $this->assertSame('+1-555-0199', WordPressMockState::$options['_theme_option_phone_number']);

        Option::delete('phone_number');
        $this->assertNull(Option::get('phone_number'));
    }
}
