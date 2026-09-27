# Gallery Fields

The `gallery` field type provides a complete multi-image manager with drag-and-drop reordering.

---

## Example Usage

```php
use YourVendor\ThemeFramework\Field;

Field::add('project_photos', [
    'label'     => 'Project Photos',
    'type'      => 'gallery',
    'post_type' => 'project',
]);
```

---

## Features

* **Multi-Select**: Select multiple images simultaneously in the native Media Library.
* **Drag-and-Drop Reordering**: Interactive sorting using jQuery UI Sortable.
* **Removal**: Remove individual images by clicking the delete badge.
* **Clear Gallery**: One-click clear for all images.
* **Empty Gallery Support**: Gracefully persists and handles empty states.
* **Storage**: Stores an array of integer attachment IDs: `[101, 102, 103]`.

---

## Querying in Templates

Use `Field::images()` to get an array of formatted image URLs for any registered image size:

```php
use YourVendor\ThemeFramework\Field;

$images = Field::images('project_photos', get_the_ID(), 'large');

foreach ($images as $imageUrl) {
    echo '<img src="' . esc_url($imageUrl) . '" alt="" loading="lazy" />';
}
```

Or retrieve the raw array of attachment IDs using `Field::get()`:

```php
$attachmentIds = (array) Field::get('project_photos', get_the_ID(), []);

foreach ($attachmentIds as $id) {
    echo wp_get_attachment_image($id, 'thumbnail');
}
```
