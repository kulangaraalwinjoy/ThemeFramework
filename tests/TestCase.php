<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use KulangaraAlwinJoy\ThemeFramework\Field\FieldRegistry;
use KulangaraAlwinJoy\ThemeFramework\Field\FieldType;
use KulangaraAlwinJoy\ThemeFramework\Framework;
use KulangaraAlwinJoy\ThemeFramework\Group\FieldGroupRegistry;
use KulangaraAlwinJoy\ThemeFramework\Menu\MenuFieldRegistry;
use KulangaraAlwinJoy\ThemeFramework\Option\OptionRegistry;
use KulangaraAlwinJoy\ThemeFramework\PostType\Registry as PostTypeRegistry;
use KulangaraAlwinJoy\ThemeFramework\Support\Validator;

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
