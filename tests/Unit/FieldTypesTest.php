<?php

declare(strict_types=1);

namespace YourVendor\ThemeFramework\Tests\Unit;

use YourVendor\ThemeFramework\Field\Contracts\FieldTypeInterface;
use YourVendor\ThemeFramework\Field\Field;
use YourVendor\ThemeFramework\Field\FieldRegistry;
use YourVendor\ThemeFramework\Field\FieldType;
use YourVendor\ThemeFramework\Field\Types\BaseFieldType;
use YourVendor\ThemeFramework\Field\Types\CheckboxField;
use YourVendor\ThemeFramework\Field\Types\CustomField;
use YourVendor\ThemeFramework\Field\Types\EmailField;
use YourVendor\ThemeFramework\Field\Types\GalleryField;
use YourVendor\ThemeFramework\Field\Types\MediaField;
use YourVendor\ThemeFramework\Field\Types\NumberField;
use YourVendor\ThemeFramework\Field\Types\SelectField;
use YourVendor\ThemeFramework\Field\Types\TextareaField;
use YourVendor\ThemeFramework\Field\Types\TextField;
use YourVendor\ThemeFramework\Field\Types\UrlField;
use YourVendor\ThemeFramework\Tests\TestCase;
use YourVendor\ThemeFramework\Tests\WordPressMockState;

class FieldTypesTest extends TestCase
{
    public function testTextField(): void
    {
        $type = FieldType::get('text');
        $this->assertInstanceOf(TextField::class, $type);

        $sanitized = $type->sanitize('<b>Hello</b> World', ['id' => 'test']);
        $this->assertSame('Hello World', $sanitized);

        ob_start();
        $type->render('test_field', 'Sample Value', ['id' => 'test_field']);
        $html = ob_get_clean();

        $this->assertStringContainsString('type="text"', $html);
        $this->assertStringContainsString('name="test_field"', $html);
        $this->assertStringContainsString('value="Sample Value"', $html);
    }

    public function testTextareaField(): void
    {
        $type = FieldType::get('textarea');
        $this->assertInstanceOf(TextareaField::class, $type);

        $sanitized = $type->sanitize('Line 1<script>alert(1)</script>', ['id' => 'test']);
        $this->assertSame('Line 1alert(1)', $sanitized);

        ob_start();
        $type->render('desc', 'Content', ['id' => 'desc', 'rows' => 8]);
        $html = ob_get_clean();

        $this->assertStringContainsString('<textarea', $html);
        $this->assertStringContainsString('rows="8"', $html);
        $this->assertStringContainsString('Content</textarea>', $html);
    }

    public function testNumberField(): void
    {
        $type = FieldType::get('number');
        $this->assertInstanceOf(NumberField::class, $type);

        $this->assertSame(42, $type->sanitize('42', ['id' => 'num']));
        $this->assertSame(42.5, $type->sanitize('42.5', ['id' => 'num', 'step' => '0.1']));
        $this->assertNull($type->sanitize('invalid', ['id' => 'num']));

        ob_start();
        $type->render('price', 99, ['id' => 'price', 'min' => 10, 'max' => 100]);
        $html = ob_get_clean();

        $this->assertStringContainsString('type="number"', $html);
        $this->assertStringContainsString('min="10"', $html);
        $this->assertStringContainsString('max="100"', $html);
        $this->assertStringContainsString('value="99"', $html);
    }

    public function testUrlField(): void
    {
        $type = FieldType::get('url');
        $this->assertInstanceOf(UrlField::class, $type);

        $this->assertSame('https://example.com', $type->sanitize('https://example.com', ['id' => 'url']));
        $this->assertSame('', $type->sanitize('javascript:alert(1)', ['id' => 'url']));
    }

    public function testEmailField(): void
    {
        $type = FieldType::get('email');
        $this->assertInstanceOf(EmailField::class, $type);

        $this->assertSame('test@example.com', $type->sanitize('test@example.com', ['id' => 'email']));
        $this->assertSame('', $type->sanitize('not-an-email', ['id' => 'email']));
    }

    public function testCheckboxField(): void
    {
        $type = FieldType::get('checkbox');
        $this->assertInstanceOf(CheckboxField::class, $type);

        $this->assertSame(1, $type->sanitize('1', ['id' => 'check']));
        $this->assertSame(1, $type->sanitize('on', ['id' => 'check']));
        $this->assertSame(0, $type->sanitize('0', ['id' => 'check']));
        $this->assertSame(0, $type->sanitize('', ['id' => 'check']));

        ob_start();
        $type->render('is_active', 1, ['id' => 'is_active', 'checkbox_label' => 'Active Item']);
        $html = ob_get_clean();

        $this->assertStringContainsString('type="hidden" name="is_active" value="0"', $html);
        $this->assertStringContainsString('type="checkbox"', $html);
        $this->assertStringContainsString('checked="checked"', $html);
        $this->assertStringContainsString('Active Item', $html);
    }

    public function testSelectField(): void
    {
        $type = FieldType::get('select');
        $this->assertInstanceOf(SelectField::class, $type);

        $choices = ['draft' => 'Draft', 'publish' => 'Published'];
        $field = ['id' => 'status', 'choices' => $choices];

        $this->assertSame('publish', $type->sanitize('publish', $field));
        $this->assertNull($type->sanitize('hacked', $field));

        // Multiple
        $fieldMulti = ['id' => 'tags', 'choices' => $choices, 'multiple' => true];
        $this->assertSame(['draft'], $type->sanitize(['draft', 'invalid'], $fieldMulti));
    }

    public function testMediaFieldWithAllowedMimes(): void
    {
        $type = FieldType::get('media');
        $this->assertInstanceOf(MediaField::class, $type);

        // Setup mock attachments
        WordPressMockState::$posts[101] = (object) [
            'ID' => 101,
            'post_type' => 'attachment',
            'post_mime_type' => 'image/png',
            'post_title' => 'Sample PNG',
            'guid' => 'https://example.com/wp-content/uploads/icon.png',
        ];

        WordPressMockState::$posts[102] = (object) [
            'ID' => 102,
            'post_type' => 'attachment',
            'post_mime_type' => 'application/pdf',
            'post_title' => 'Sample PDF',
            'guid' => 'https://example.com/wp-content/uploads/doc.pdf',
        ];

        $fieldStrict = [
            'id' => 'icon',
            'allowed_types' => ['image/png'],
        ];

        // PNG allowed
        $this->assertSame(101, $type->sanitize(101, $fieldStrict));
        // PDF not allowed
        $this->assertNull($type->sanitize(102, $fieldStrict));
        // Non-existent attachment
        $this->assertNull($type->sanitize(999, $fieldStrict));

        // Helper methods on Field facade
        WordPressMockState::$postMeta[50]['_theme_icon'] = 101;
        $this->assertSame('https://example.com/wp-content/uploads/icon.png', Field::url('icon', 50));

        $att = Field::attachment('icon', 50);
        $this->assertNotNull($att);
        $this->assertSame(101, $att['id']);
        $this->assertSame('image/png', $att['mime_type']);
    }

    public function testGalleryField(): void
    {
        $type = FieldType::get('gallery');
        $this->assertInstanceOf(GalleryField::class, $type);

        WordPressMockState::$posts[201] = (object) [
            'ID' => 201,
            'post_type' => 'attachment',
            'post_mime_type' => 'image/jpeg',
            'post_title' => 'Photo 1',
            'guid' => 'https://example.com/img1.jpg',
        ];
        WordPressMockState::$posts[202] = (object) [
            'ID' => 202,
            'post_type' => 'attachment',
            'post_mime_type' => 'image/png',
            'post_title' => 'Photo 2',
            'guid' => 'https://example.com/img2.png',
        ];

        $field = ['id' => 'gallery'];

        // Comma-separated or array
        $this->assertSame([201, 202], $type->sanitize('201,202', $field));
        $this->assertSame([201], $type->sanitize([201, 999], $field));

        // Field::images helper
        WordPressMockState::$postMeta[60]['_theme_gallery'] = [201, 202];
        $images = Field::images('gallery', 60);
        $this->assertCount(2, $images);
        $this->assertSame('https://example.com/img1.jpg', $images[0]);
        $this->assertSame('https://example.com/img2.png', $images[1]);
    }

    public function testCustomFieldCallback(): void
    {
        $rendered = false;
        Field::add('custom_test', [
            'label' => 'Custom',
            'type' => 'custom',
            'render' => function ($field, $value, $objectId) use (&$rendered) {
                $rendered = true;
                echo '<div>Custom rendered ' . esc_html((string) $value) . '</div>';
            },
            'sanitize' => function ($value, $field) {
                return 'PREFIX_' . $value;
            },
        ]);

        $type = FieldType::get('custom');
        $this->assertInstanceOf(CustomField::class, $type);

        $sanitized = $type->sanitize('data', FieldRegistry::get('custom_test'));
        $this->assertSame('PREFIX_data', $sanitized);

        ob_start();
        $type->render('custom_test', 'Foo', FieldRegistry::get('custom_test'));
        $output = ob_get_clean();

        $this->assertTrue($rendered);
        $this->assertStringContainsString('Custom rendered Foo', $output);
    }

    public function testThirdPartyFieldTypeRegistration(): void
    {
        $mockFieldType = new class extends BaseFieldType {
            public function render(string $name, mixed $value, array $field): void
            {
                echo '<input type="color" name="' . esc_attr($name) . '" value="' . esc_attr((string) $value) . '" />';
            }

            public function sanitize(mixed $value, array $field): mixed
            {
                return preg_match('/^#[a-f0-9]{6}$/i', (string) $value) ? $value : '#000000';
            }
        };

        FieldType::register('color_picker', fn () => $mockFieldType);

        $this->assertTrue(FieldType::has('color_picker'));
        $handler = FieldType::get('color_picker');
        $this->assertSame('#ff0000', $handler->sanitize('#ff0000', []));
        $this->assertSame('#000000', $handler->sanitize('not-a-color', []));
    }
}
