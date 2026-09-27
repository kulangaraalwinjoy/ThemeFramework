<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Tests\Unit;

use KulangaraAlwinJoy\ThemeFramework\Support\Conditions;
use KulangaraAlwinJoy\ThemeFramework\Tests\TestCase;

class ConditionsTest extends TestCase
{
    public function testEqualsCondition(): void
    {
        $conditions = [
            [
                'field' => 'project_type',
                'compare' => '==',
                'value' => 'video',
            ],
        ];

        $matched = Conditions::evaluate($conditions, fn ($field) => 'video');
        $this->assertTrue($matched);

        $notMatched = Conditions::evaluate($conditions, fn ($field) => 'audio');
        $this->assertFalse($notMatched);
    }

    public function testNotEqualsCondition(): void
    {
        $conditions = [
            [
                'field' => 'status',
                'compare' => '!=',
                'value' => 'archived',
            ],
        ];

        $this->assertTrue(Conditions::evaluate($conditions, fn ($field) => 'active'));
        $this->assertFalse(Conditions::evaluate($conditions, fn ($field) => 'archived'));
    }

    public function testNumericComparisons(): void
    {
        $conditions = [
            ['field' => 'age', 'compare' => '>=', 'value' => 18],
            ['field' => 'score', 'compare' => '<', 'value' => 100],
        ];

        $matched = Conditions::evaluate($conditions, fn ($field) => $field === 'age' ? 20 : 95);
        $this->assertTrue($matched);

        $notMatched = Conditions::evaluate($conditions, fn ($field) => $field === 'age' ? 16 : 95);
        $this->assertFalse($notMatched);
    }

    public function testInAndNotIn(): void
    {
        $inCond = [['field' => 'format', 'compare' => 'IN', 'value' => ['video', 'gallery']]];
        $this->assertTrue(Conditions::evaluate($inCond, fn () => 'video'));
        $this->assertFalse(Conditions::evaluate($inCond, fn () => 'text'));

        $notInCond = [['field' => 'format', 'compare' => 'NOT IN', 'value' => ['video', 'gallery']]];
        $this->assertTrue(Conditions::evaluate($notInCond, fn () => 'text'));
        $this->assertFalse(Conditions::evaluate($notInCond, fn () => 'video'));
    }

    public function testToJsonAttribute(): void
    {
        $conditions = [['field' => 'type', 'compare' => '==', 'value' => 'special']];
        $attr = Conditions::toJsonAttribute($conditions);
        $this->assertStringContainsString('special', $attr);
    }
}
