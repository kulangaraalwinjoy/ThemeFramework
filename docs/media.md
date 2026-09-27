# Media Fields

The `media` field type integrates directly with the native WordPress Media Library modal.

---

## Example Usage

```php
use YourVendor\ThemeFramework\Field;

Field::add('company_logo', [
    'label'         => 'Company Logo',
    'type'          => 'media',
    'post_type'     => 'project',
    'allowed_types' => [
        'image/png',
        'image/svg+xml',
    ],
]);
```

---

## Features

* **Native Media Modal**: Leverages WordPress's built-in `wp.media` modal.
* **Previewing**: Automatically renders thumbnail previews for images and file icons for non-image attachments.
* **Action Buttons**: Select, Replace, and Remove buttons.
* **Attachment ID Storage**: Stores the integer ID of the attachment (never raw or brittle URLs).
* **MIME Restriction**: Restricts allowed files in the modal and validates them server-side.

---

## MIME Type Restrictions

You can specify MIME constraints using `allowed_types` or the alias `library`:

```php
// Using allowed_types
Field::add('document', [
    'label'         => 'PDF Document',
    'type'          => 'media',
    'allowed_types' => ['application/pdf'],
]);

// Or using library alias
Field::add('avatar', [
    'label'   => 'Avatar Image',
    'type'    => 'media',
    'library' => ['image/jpeg', 'image/png'],
]);
```

### Server-Side Validation

The framework **never trusts the browser** for MIME type validation. When data is saved:
1. The submitted ID is verified to be a valid post of type `attachment`.
2. The attachment's actual server-recorded MIME type (`get_post_mime_type()`) is checked against the configured allowed types.
3. If validation fails, the input is discarded.

---

## Safe SVG Handling

When allowing `image/svg+xml`:
* SVGs must be uploaded by users with appropriate capabilities or through plugins that sanitize SVG XML on upload (e.g. Safe SVG).
* The framework verifies the MIME type server-side and checks attachment status before persisting.

---

## Querying in Templates

```php
use YourVendor\ThemeFramework\Field;

// 1. Get attachment URL directly
$logoUrl = Field::url('company_logo', $postId, 'large');

// 2. Get full attachment metadata
$attachment = Field::attachment('company_logo', $postId);
/*
$attachment contains:
[
    'id'          => 123,
    'url'         => 'https://example.com/...',
    'title'       => 'Logo Title',
    'caption'     => '...',
    'description' => '...',
    'alt'         => 'Logo Alt Text',
    'mime_type'   => 'image/png',
    'width'       => 400,
    'height'      => 120,
]
*/
```
