<?php

declare(strict_types=1);

namespace YourVendor\ThemeFramework\Tests\Unit;

use YourVendor\ThemeFramework\Field\Field;
use YourVendor\ThemeFramework\Field\FieldSaver;
use YourVendor\ThemeFramework\Security\Capability;
use YourVendor\ThemeFramework\Security\Nonce;
use YourVendor\ThemeFramework\Security\Sanitizer;
use YourVendor\ThemeFramework\Tests\TestCase;
use YourVendor\ThemeFramework\Tests\WordPressMockState;

class SecurityTest extends TestCase
{
    public function testNonceGenerationAndVerification(): void
    {
        $action = 'my_action';
        $nonce = Nonce::create($action);

        $this->assertNotEmpty($nonce);
        $this->assertTrue(Nonce::verify($nonce, $action));
        $this->assertFalse(Nonce::verify('wrong-nonce', $action));
        $this->assertFalse(Nonce::verify(null, $action));
    }

    public function testCapabilityChecks(): void
    {
        WordPressMockState::$capabilities['edit_post'] = true;
        $this->assertTrue(Capability::canEditPost(123));

        WordPressMockState::$capabilities['edit_post'] = false;
        $this->assertFalse(Capability::canEditPost(123));

        WordPressMockState::$capabilities['manage_options'] = true;
        $this->assertTrue(Capability::canManageOptions());

        WordPressMockState::$capabilities['manage_options'] = false;
        $this->assertFalse(Capability::canManageOptions());
    }

    public function testFieldSaverRejectsInvalidNonce(): void
    {
        $saver = new FieldSaver();
        $fields = [
            'location' => [
                'id' => 'location',
                'label' => 'Location',
                'type' => 'text',
            ],
        ];

        $postData = [
            '_theme_framework_nonce' => 'invalid-nonce',
            'location' => 'Paris',
        ];

        $saved = $saver->save(99, $fields, Field::getStorage(), $postData, 'post_fields_99');
        $this->assertFalse($saved);
        $this->assertNull(Field::get('location', 99));
    }

    public function testFieldSaverRejectsUnauthorizedUser(): void
    {
        $saver = new FieldSaver();
        $fields = [
            'location' => [
                'id' => 'location',
                'label' => 'Location',
                'type' => 'text',
            ],
        ];

        $postData = [
            '_theme_framework_nonce' => Nonce::create('post_fields_99'),
            'location' => 'Paris',
        ];

        // Deny permission
        WordPressMockState::$capabilities['edit_post'] = false;

        $saved = $saver->save(99, $fields, Field::getStorage(), $postData, 'post_fields_99');
        $this->assertFalse($saved);
        $this->assertNull(Field::get('location', 99));
    }

    public function testSecurityForCustomJsAndCss(): void
    {
        // Low-privilege user cannot save unfiltered JS
        WordPressMockState::$capabilities['unfiltered_html'] = false;
        $this->assertSame('', Sanitizer::javascript('<script>alert("hack")</script>'));

        // Admin with unfiltered_html can save JS
        WordPressMockState::$capabilities['unfiltered_html'] = true;
        $this->assertSame('console.log("hello");', Sanitizer::javascript('console.log("hello");'));

        // CSS strips HTML tags
        $this->assertSame('body { color: red; }', Sanitizer::css('<style>body { color: red; }</style>'));
    }
}
