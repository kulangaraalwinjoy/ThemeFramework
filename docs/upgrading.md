# Upgrading & Versioning Guide

`kulangaraalwinjoy/wp-theme-framework` strictly follows [Semantic Versioning 2.0.0](https://semver.org/).

---

## Semantic Versioning Principles

* **MAJOR (`1.x.x` &rarr; `2.0.0`)**: Incompatible API changes.
* **MINOR (`1.0.x` &rarr; `1.1.0`)**: Backwards-compatible new features.
* **PATCH (`1.0.0` &rarr; `1.0.1`)**: Backwards-compatible bug fixes and security patches.

---

## Public API Stability Guarantee

The following interfaces are considered stable public API. Their signatures and behaviors will not change in breaking ways during `1.x`:

* `PostType::register()`, `PostType::assets()`
* `Field::add()`, `Field::get()`, `Field::set()`, `Field::delete()`, `Field::url()`, `Field::attachment()`, `Field::images()`
* `MenuField::add()`, `MenuField::get()`, `MenuField::set()`, `MenuField::delete()`, `MenuField::url()`
* `OptionPage::register()`
* `Option::add()`, `Option::get()`, `Option::set()`, `Option::delete()`, `Option::url()`, `Option::attachment()`
* `FieldGroup::add()`
* `FieldType::register()`
* `Assets::registerStyle()`, `Assets::registerScript()`, `Assets::enqueueStyle()`, `Assets::enqueueScript()`

---

## Upgrading via Composer

To upgrade to the latest compatible minor or patch release:

```bash
composer update kulangaraalwinjoy/wp-theme-framework
```

Always review [`CHANGELOG.md`](../CHANGELOG.md) before performing major version upgrades.
