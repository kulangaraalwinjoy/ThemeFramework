<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Tests\Unit;

use KulangaraAlwinJoy\ThemeFramework\Field\FieldRegistry;
use KulangaraAlwinJoy\ThemeFramework\Group\FieldGroup;
use KulangaraAlwinJoy\ThemeFramework\Group\FieldGroupRegistry;
use KulangaraAlwinJoy\ThemeFramework\Tests\TestCase;
use KulangaraAlwinJoy\ThemeFramework\Tests\WordPressMockState;

class FieldGroupTest extends TestCase
{
    public function testFieldGroupRegistersNestedFields(): void
    {
        FieldGroup::add('project_details', [
            'title' => 'Project Details',
            'post_type' => 'project',
            'fields' => [
                'location' => [
                    'label' => 'Location',
                    'type' => 'text',
                ],
                'icon' => [
                    'label' => 'Icon',
                    'type' => 'media',
                ],
            ],
        ]);

        $group = FieldGroupRegistry::get('project_details');
        $this->assertNotNull($group);
        $this->assertSame('Project Details', $group['title']);

        // Check that nested fields were registered in FieldRegistry with inherited post_type
        $this->assertTrue(FieldRegistry::has('location'));
        $this->assertTrue(FieldRegistry::has('icon'));

        $location = FieldRegistry::get('location');
        $this->assertSame(['project'], $location['post_types']);
        $this->assertSame('project_details', $location['group']);

        // Check meta box registration
        FieldGroup::registerMetaBoxes('project');
        $this->assertArrayHasKey('theme_group_project_details', WordPressMockState::$metaBoxes);
        $this->assertSame('Project Details', WordPressMockState::$metaBoxes['theme_group_project_details']['title']);
    }
}
