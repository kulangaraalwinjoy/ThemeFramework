<?php

declare(strict_types=1);

namespace KulangaraAlwinJoy\ThemeFramework\Tests\Unit;

use InvalidArgumentException;
use KulangaraAlwinJoy\ThemeFramework\PostType\PostType;
use KulangaraAlwinJoy\ThemeFramework\PostType\Registry;
use KulangaraAlwinJoy\ThemeFramework\Tests\TestCase;
use KulangaraAlwinJoy\ThemeFramework\Tests\WordPressMockState;

class PostTypeTest extends TestCase
{
    public function testRegisterPostType(): void
    {
        PostType::register('project', [
            'label' => 'Projects',
            'singular_label' => 'Project',
            'public' => true,
            'supports' => ['title', 'editor', 'thumbnail'],
        ]);

        $this->assertNotNull(Registry::get('project'));

        // Trigger registration
        PostType::registerAll();

        $this->assertArrayHasKey('project', WordPressMockState::$postTypes);
        $registered = WordPressMockState::$postTypes['project'];

        $this->assertTrue($registered['public']);
        $this->assertSame(['title', 'editor', 'thumbnail'], $registered['supports']);
        $this->assertSame('Projects', $registered['labels']['name']);
        $this->assertSame('Project', $registered['labels']['singular_name']);
    }

    public function testEmptySlugThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PostType::register('', ['label' => 'Empty']);
    }

    public function testPostTypeAssetsRegistration(): void
    {
        PostType::assets('project', [
            'admin' => [
                'css' => ['project-admin-css'],
                'js' => ['project-admin-js'],
            ],
            'frontend' => [
                'css' => ['project-frontend-css'],
                'js' => ['project-frontend-js'],
            ],
        ]);

        $assets = Registry::getAssets('project');
        $this->assertSame(['project-admin-css'], $assets['admin']['css']);
        $this->assertSame(['project-admin-js'], $assets['admin']['js']);
        $this->assertSame(['project-frontend-css'], $assets['frontend']['css']);
        $this->assertSame(['project-frontend-js'], $assets['frontend']['js']);
    }
}
