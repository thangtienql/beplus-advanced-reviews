---
name: bpss-php
description: Use when writing or editing PHP code under src/, includes/, or the bootstrap file beplus-advanced-reviews-for-woocommerce.php. Covers PSR-4 namespaces, modules, helpers, security, naming, and coding standards for Beplus Advanced Reviews For Woocommerce.
---

# Beplus Advanced Reviews For Woocommerce — PHP

## Quality gate

Before finishing PHP work:

1. Every plugin-owned PHP file begins with `if ( ! defined( 'ABSPATH' ) ) { exit; }`.
2. New classes under `src/` use the `BeplusAdvancedReviewsForWoocommerce\...` namespace that matches their path.
3. Global helper functions use the `beplus_advanced_reviews_for_woocommerce_` prefix and include complete PHPDoc when they are part of a shared API.
4. Keep hook registration inside modules or service classes; avoid top-level side effects in included files.
5. If the plugin has PHP linting or static analysis configured, run it on changed files and fix introduced violations.

## Conventions

- **Bootstrap:** Constants and boot functions live in `beplus-advanced-reviews-for-woocommerce.php`; business logic lives in `src/`.
- **Modules:** Extend `AbstractModule`; constructors receive dependencies from the container; implement `register(): void`.
- **Services:** Resolve shared services from `Container::get()` rather than instantiating repeated singletons in random files.
- **Settings:** Centralize option defaults and migration logic in `SettingsRegistry`. Display mode settings control placement (keep / replace / custom hook).
- **Templates:** Escape all dynamic output in PHP templates; use contextual escaping for URLs, attributes, and HTML.
- **Script data:** Localize front-end and editor data through the asset loader with a dedicated object for the plugin.
- **Display mode:** Placement logic lives in `src/Core/Placement.php` — handles keep / replace / custom hook modes.

## Naming

| Type | Pattern | Example |
|------|---------|---------|
| Class | PascalCase | `ReviewController`, `SettingsRegistry` |
| Abstract | `Abstract` + name | `AbstractModule` |
| Global helper | `beplus_advanced_reviews_for_woocommerce_*` | `beplus_advanced_reviews_for_woocommerce_get_settings()` |
| Hook constant | `HookManager::CONST` | `SERVICES`, `BLOCKS` |

## Infrastructure patterns

Follow existing classes in `src/Core/Plugin.php`, `src/Core/Container.php`, `src/Core/Placement.php`, `src/Settings/SettingsRegistry.php`, and `src/REST/*Controller.php` when adding new infrastructure.

```text
❌ function get_review_settings() without prefix in includes/common.php
✅ function beplus_advanced_reviews_for_woocommerce_get_review_settings()

❌ new ReviewController() at file load in beplus-advanced-reviews-for-woocommerce.php
✅ Resolve it through the container during plugin boot
```

## Reference

| File | Purpose |
|------|---------|
| `beplus-advanced-reviews-for-woocommerce.php` | Bootstrap pattern |
| `src/Core/Plugin.php` | Boot flow, activate/deactivate |
| `src/Core/AbstractModule.php` | Module base |
| `src/Core/Container.php` | DI container |
| `src/Core/Placement.php` | Display mode logic |
| `src/Settings/SettingsRegistry.php` | Settings pattern |
| `includes/common.php` | Global helpers |
