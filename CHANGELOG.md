# Changelog

All notable changes to `kulangaraalwinjoy/wp-theme-framework` will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-09-27

### Added
- **Unified Field Engine**: Single shared engine powering Post Meta, Menu Item Meta, and Theme Options.
- **Custom Post Types**: Declarative registration with `PostType::register()` and asset bundling with `PostType::assets()`.
- **Field API**: Declarative fields (`Field::add()`) with full lifecycle handling (`Field::get()`, `Field::set()`, `Field::delete()`).
- **Built-in Field Types**:
  - `text`
  - `textarea`
  - `number`
  - `url`
  - `email`
  - `checkbox`
  - `select`
  - `media` (single attachment with native WP Media Library, preview, replace, remove, server-side MIME check)
  - `gallery` (multi-image picker with reordering sortable grid, preview, remove)
  - `custom` (callable escape hatch for custom rendering)
- **Menu Custom Fields**: Declarative menu item meta via native `wp_nav_menu_item_custom_fields` with `MenuField::add()`.
- **Option Pages & Options**: Native Settings API integration with `OptionPage::register()` and `Option::add()`.
- **Field Groups**: Grouping fields into custom meta boxes using `FieldGroup::add()`.
- **Validation Engine**: Extensible validation rules (`required`, `min`, `max`, `min_length`, `max_length`, `email`, `url`, `in`, `regex`).
- **Sanitization Layer**: Specialized sanitizers per field type with safe SVG checks and capability-guarded JS/CSS sanitization.
- **Security & Authorization**: Automatic nonce generation/verification and capability checks (`edit_post`, `manage_options`, `edit_theme_options`, `unfiltered_html`).
- **Asset Manager**: Conditional, screen-aware asset loading for admin and frontend styles and scripts.
- **WordPress REST API Integration**: Automatic post meta registration via `register_post_meta()` when `show_in_rest` is enabled.
- **Extensibility Hooks**: Public actions and filters for field definitions, rendering, values, and saving.
- **Comprehensive Test Suite**: PHPUnit tests with 100% pass rate and realistic end-to-end WordPress integration tests.
- **Static Analysis & Standards**: PHPStan Level 7 and WordPress Coding Standards compliance.
