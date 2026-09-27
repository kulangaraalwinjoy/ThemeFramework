<?php

declare(strict_types=1);

namespace YourVendor\ThemeFramework\Field;

use YourVendor\ThemeFramework\Support\Conditions;

/**
 * Universal field renderer for all contexts (Posts, Menus, Options).
 */
class FieldRenderer
{
    /**
     * Render a standard field row with label, control, and description.
     *
     * @param array<string, mixed> $field
     * @param mixed $value
     * @param string|null $inputName
     * @param mixed $objectId
     * @return void
     */
    public function renderRow(
        array $field,
        mixed $value,
        ?string $inputName = null,
        mixed $objectId = null
    ): void {
        $id = (string) ($field['id'] ?? '');
        $inputName = $inputName ?? $id;
        $label = (string) ($field['label'] ?? '');
        $description = (string) ($field['description'] ?? '');
        $isRequired = !empty($field['validation']['required']) || !empty($field['required']);
        $conditions = (array) ($field['conditions'] ?? []);
        $conditionsJson = Conditions::toJsonAttribute($conditions);
        $type = (string) ($field['type'] ?? 'text');

        // Pass current objectId into field definition for callbacks
        $field['_current_object_id'] = $objectId;

        do_action('theme_framework_before_field_render', $field, $value, $objectId);

        ?>
        <div class="theme-framework-field theme-framework-field-<?php echo esc_attr($type); ?>"
             id="theme-framework-field-wrap-<?php echo esc_attr($id); ?>"
             data-field-id="<?php echo esc_attr($id); ?>"
             data-field-type="<?php echo esc_attr($type); ?>"
             <?php if ($conditionsJson !== '') : ?>
                 data-conditions="<?php echo $conditionsJson; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"
             <?php endif; ?>>

            <?php if ($type !== 'checkbox' || !empty($field['show_row_label'])) : ?>
                <div class="theme-framework-field-label-wrap">
                    <label for="<?php echo esc_attr($id); ?>" class="theme-framework-field-label">
                        <?php echo esc_html($label); ?>
                        <?php if ($isRequired) : ?>
                            <span class="theme-framework-required" aria-hidden="true">*</span>
                        <?php endif; ?>
                    </label>
                </div>
            <?php endif; ?>

            <div class="theme-framework-field-control">
                <?php
                $this->renderControl($field, $value, $inputName, $objectId);

                if ($description !== '') :
                    ?>
                    <p class="description theme-framework-description"><?php echo esc_html($description); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php

        do_action('theme_framework_field_rendered', $field, $value, $objectId);
        do_action('theme_framework_after_field_render', $field, $value, $objectId);
    }

    /**
     * Render only the field control (useful for WordPress Settings API tables).
     *
     * @param array<string, mixed> $field
     * @param mixed $value
     * @param string $inputName
     * @param mixed $objectId
     * @return void
     */
    public function renderControl(
        array $field,
        mixed $value,
        string $inputName,
        mixed $objectId = null
    ): void {
        $type = (string) ($field['type'] ?? 'text');
        $field['_current_object_id'] = $objectId;

        $handler = FieldType::get($type);
        $handler->render($inputName, $value, $field);
    }
}
