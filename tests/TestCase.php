<?php

declare(strict_types=1);

namespace YourVendor\ThemeFramework\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use YourVendor\ThemeFramework\Field\FieldRegistry;
use YourVendor\ThemeFramework\Field\FieldType;
use YourVendor\ThemeFramework\Framework;
use YourVendor\ThemeFramework\Group\FieldGroupRegistry;
use YourVendor\ThemeFramework\Menu\MenuFieldRegistry;
use YourVendor\ThemeFramework\Option\OptionRegistry;
use YourVendor\ThemeFramework\PostType\Registry as PostTypeRegistry;
use YourVendor\ThemeFramework\Support\Validator;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        WordPressMockState::reset();
        Framework::resetBoot();
        FieldRegistry::reset();
        FieldType::reset();
        FieldGroupRegistry::reset();
        MenuFieldRegistry::reset();
        OptionRegistry::reset();
        PostTypeRegistry::reset();
        Validator::resetCustomRules();

        Framework::boot();
    }
}
