<?php

declare(strict_types=1);

namespace YourVendor\ThemeFramework\Tests\Unit;

use InvalidArgumentException;
use YourVendor\ThemeFramework\Field\Field;
use YourVendor\ThemeFramework\Field\FieldRegistry;
use YourVendor\ThemeFramework\Field\FieldResolver;
use YourVendor\ThemeFramework\Tests\TestCase;
use YourVendor\ThemeFramework\Tests\WordPressMockState;

class FieldTest extends TestCase
{
    public function testFieldAddAndGet(): void
    {
        Field::add('location', [
            'label' => 'Location',
            'type' => 'text',
            'post_type' => 'project',
        ]);

        $this->assertTrue(FieldRegistry::has('location'));

        $field = FieldRegistry::get('location');
        $this->assertNotNull($field);
        $this->assertSame('Location', $field['label']);
        $this->assertSame(['project'], $field['post_types']);

        // Set value
        $postId = 10;
        Field::set('location', $postId, 'Amsterdam');

        // Verify stored meta
        $this->assertSame('Amsterdam', Field::get('location', $postId));
        $this->assertSame('Amsterdam', WordPressMockState::$postMeta[10]['_theme_location']);

        // Delete value
        Field::delete('location', $postId);
        $this->assertNull(Field::get('location', $postId));
    }

    public function testFieldValidationMissingLabelThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Field::add('no_label', [
            'type' => 'text',
        ]);
    }

    public function testFieldValidationUnknownTypeThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        Field::add('invalid_type', [
            'label' => 'Invalid',
            'type' => 'non_existent_type',
        ]);
    }

    public function testFieldResolverMultiplePostTypesAndWildcard(): void
    {
        Field::add('field_specific', [
            'label' => 'Specific',
            'type' => 'text',
            'post_type' => 'project',
        ]);

        Field::add('field_multiple', [
            'label' => 'Multiple',
            'type' => 'text',
            'post_types' => ['project', 'office'],
        ]);

        Field::add('field_wildcard', [
            'label' => 'Wildcard',
            'type' => 'text',
            'post_type' => '*',
        ]);

        Field::add('field_other', [
            'label' => 'Other',
            'type' => 'text',
            'post_type' => 'post',
        ]);

        $projectFields = FieldResolver::forPostType('project');
        $this->assertArrayHasKey('field_specific', $projectFields);
        $this->assertArrayHasKey('field_multiple', $projectFields);
        $this->assertArrayHasKey('field_wildcard', $projectFields);
        $this->assertArrayNotHasKey('field_other', $projectFields);

        $officeFields = FieldResolver::forPostType('office');
        $this->assertArrayNotHasKey('field_specific', $officeFields);
        $this->assertArrayHasKey('field_multiple', $officeFields);
        $this->assertArrayHasKey('field_wildcard', $officeFields);
    }
}
