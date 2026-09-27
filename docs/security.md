# Security Guide

Security is a core design requirement of `yourvendor/wp-theme-framework`. Every read, write, and render operation enforces WordPress security best practices.

---

## 1. Nonce Verification

All form submissions in the WordPress admin generate and verify cryptographic nonces:

* **Post Fields**: Uses action `post_fields_{postId}`.
* **Menu Fields**: Uses action `menu_fields`.
* **Option Pages**: Handled by the native WordPress Settings API nonce mechanism.

Write operations automatically reject requests with missing or invalid nonces.

---

## 2. Capability Checks

Every save operation verifies whether the currently logged-in user possesses the required capability:

* **Post Fields**: Requires `current_user_can('edit_post', $postId)`.
* **Menu Fields**: Requires `current_user_can('edit_theme_options')`.
* **Option Pages**: Defaults to `current_user_can('manage_options')` (configurable per page).

Unauthorized submissions are discarded immediately.

---

## 3. Strict Server-Side Sanitization

The framework never trusts `$_POST`, `$_GET`, or REST payloads. Each field type implements strict sanitization:

| Field Type | Sanitization Function / Logic |
|---|---|
| `text` | `sanitize_text_field()` |
| `textarea` | `sanitize_textarea_field()` |
| `number` | Strict numeric conversion (`(int)` or `(float)`) |
| `url` | `esc_url_raw()` |
| `email` | `sanitize_email()` |
| `checkbox` | Boolean integer normalization (`1` or `0`) |
| `select` | Validates value against whitelist of registered choices |
| `media` | Verifies attachment ID exists, is post type `attachment`, and matches allowed MIME types |
| `gallery` | Validates array of attachment IDs and removes invalid entries |

---

## 4. Custom CSS and JavaScript Security

Allowing users to edit raw code presents Cross-Site Scripting (XSS) risks:

* **Custom CSS**: Strips HTML tags (`wp_strip_all_tags()`). Requires `edit_theme_options` capability.
* **Custom JavaScript**: Strictly guarded by the WordPress `unfiltered_html` capability. If a user without `unfiltered_html` attempts to save JavaScript, the input is discarded.

### Sanitization vs. Escaping

* **Sanitization** cleans input before database storage.
* **Escaping** protects output during HTML rendering (`esc_html()`, `esc_attr()`, `esc_url()`).
Always escape output appropriately when rendering field values in theme templates.
