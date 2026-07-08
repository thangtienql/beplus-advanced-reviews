---
name: bpss-add-review-provider
description: Adds a new review provider or review data source to Beplus Advanced Reviews For Woocommerce by extending the plugin's storage, REST, and formatting layers. Use when adding imported reviews, external syncs, custom criteria sources, or review enrichment services.
---

# Beplus Advanced Reviews For Woocommerce — add a review provider

## Before you edit

- Read [`AGENTS.md`](../../../AGENTS.md) and [`Document Plugin.md`](../../../Document%20Plugin.md).
- Review `src/Reviews/`, `src/REST/ReviewController.php`, `src/Media/MediaHandler.php`, and `src/Settings/SettingsRegistry.php` for the existing review flow.

## Provider contract

Create a dedicated service or module under `src/` that produces or enriches review data.

Typical normalized review shape:

```php
array(
  'id'             => (int),
  'product_id'     => (int),
  'author'         => (string),
  'content'        => (string),
  'rating'         => (int),
  'avatar'         => (string),
  'has_images'     => (bool),
  'images'         => array(),
  'created_at'     => (string),
)
```

## Implement a provider

1. Choose whether the provider is an importer, sync service, formatter, or enrichment layer.
2. Keep provider-specific logic in a dedicated class or module under `src/Reviews/`, `src/Media/`, or `src/Services/`.
3. Normalize data before storing or exposing it via REST.
4. Reuse plugin validation, sanitization, and media handling paths whenever possible.
5. Expose configuration in the settings registry rather than scattering options.

## Register the provider

- Wire it through the container or a dedicated module `register()` method.
- If it needs to hook into review lifecycle events, use the documented actions and filters from `HookManager`.
- If it supplies review lists or summary data, expose it through `ReviewController` or a service used by the controller.

## Common use cases

| Use case | Suggested location |
|----------|--------------------|
| Import historical reviews | `src/Reviews/ReviewImporter.php` |
| Enrich review metadata | `src/Reviews/ReviewEnricher.php` |
| Sync images or attachments | `src/Media/MediaHandler.php` |
| Add custom review REST output | `src/REST/ReviewController.php` |

## Checklist

- [ ] Provider is in a dedicated class or module under `src/`.
- [ ] Input is sanitized and output is normalized.
- [ ] Capability and nonce checks are explicit for admin or write paths.
- [ ] Image handling follows the plugin's validation and storage rules.
- [ ] REST output and front-end rendering consume the same normalized shape.
