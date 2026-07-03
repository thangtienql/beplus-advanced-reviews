# BePlus Advanced Reviews — Agent Briefing

Use this file when changing code under `wp-content/plugins/beplus-advanced-reviews/`. **Architecture and naming standards** live in [`Document Plugin.md`](./Document Plugin.md).

## opencode rules and skills

- **Always-on context:** This file (`AGENTS.md`) and [`Document Plugin.md`](./Document Plugin.md) are loaded as project instructions.
- **opencode skills:** [`.opencode/skills/`](./.opencode/skills/) — domain-specific skills triggered by keyword match:
  - `bpss-php` — PHP code under `src/`, `includes/`
  - `bpss-rest` — REST API controllers under `src/REST/`
  - `bpss-blocks` — Gutenberg blocks under `blocks/`
  - `bpss-frontend` — JS/CSS under `admin/js/`, `blocks/`, `assets/`
  - `bpss-add-plugin-block` — workflow for creating new plugin blocks
  - `bpss-add-review-provider` — workflow for adding review data providers

Long-form context stays in this file and in `Document Plugin.md`; avoid duplicating large sections into skills.

## What this plugin is

- **WordPress plugin:** Advanced WooCommerce product reviews with image support, star distribution, AJAX filtering, and load more.
- **Primary block:** `advanced-review` — a Gutenberg block designed to be dropped into the WooCommerce single product template (FSE / Site Editor). Automatically applied to all Single Product pages on activation.
- **Architecture:** Container-based boot via `BePlusAdvancedReviews\Core\Plugin`; modules extend `AbstractModule` and register hooks in `register()`.
- **Stack:** PHP 7.4+ (8.0+ recommended), PSR-4 autoload under `src/`, **esbuild + TypeScript** for admin/blocks, procedural helpers in `includes/` when needed.
- **Target:** WordPress 6.0+, WooCommerce 7.0+.

## Naming and constants

| Item | Value |
|------|-------|
| Bootstrap file | `beplus-advanced-reviews.php` |
| Text domain | `beplus-advanced-reviews` |
| PHP namespace | `BePlusAdvancedReviews\` → `src/` |
| Global functions | `beplus_advanced_reviews_*` |
| Constants | `BEPLUS_ADVANCED_REVIEWS_*` |
| REST namespace | `beplus-advanced-reviews/v1` |
| Block prefix | `beplus-advanced-reviews/` |
| CSS prefix | `beplus-advanced-reviews` (BEM) |
| DB table prefix | `{wpdb->prefix}bpar_` |

## Core features

### 1. Enhanced Review Display
- **Average rating score** — aggregated star rating for the product.
- **Total review count** — number of approved reviews.
- **Star distribution chart** — bar chart showing count per star rating (1★–5★).
- **Review list** — paginated list of review cards rendered via a Gutenberg block.
- **Review card** — avatar, reviewer name, rating score, content, date, and images.

### 2. Reviews with Image Attachments
- Customers can upload **images** alongside their written review.
- **Copy/paste from clipboard** into the review form is supported.
- Accepted image formats: JPEG, PNG, WebP.
- Images are stored in the standard WordPress media library, linked to the review via a custom meta table (`{wpdb->prefix}bpar_review_media`).
- A lightbox renders images within the review card on the front end.

### 3. Review Submission Form
- Inline form to write and submit a review with a star rating and optional images.
- Supports file input (multi-select) and clipboard paste.
- Load More button for AJAX pagination.

### 4. Smart Review Filter & Sort
- A front-end filter bar lets visitors narrow the review list without a page reload.
- **Filter options:**
  - By star rating (1 ★ through 5 ★, multi-select)
  - **Images only** — show only reviews that include at least one image
  - Sort by date (newest/oldest) or rating (highest/lowest)
- Filtering is handled client-side via the block's TypeScript view script; for large datasets a REST endpoint supports server-side pagination with filter params.

### 5. Plugin Settings — Display Mode
- **Keep default** — WooCommerce's built-in reviews remain as-is; the block can be placed manually.
- **Replace default** — completely replaces the standard WooCommerce reviews tab/area with the Advanced Reviews block.
- Display mode logic lives in `src/Core/Placement.php`.

## Files you usually touch

| Area | Edit (source) | Do not edit as source |
|------|----------------|-----------------------|
| Bootstrap / activation | `beplus-advanced-reviews.php` | — |
| Core / domain PHP | `src/**/*.php` | — |
| Global helpers | `includes/common.php`, `includes/hooks.php` | — |
| Admin settings JS | `admin/js/settings.ts` | `admin/js/settings.js`, `admin/js/settings.asset.php` |
| Advanced Review block | `blocks/advanced-review/index.tsx`, `edit.tsx`, `view.ts` | `blocks/advanced-review/index.js`, `index.asset.php`, `view.js` |
| PHP templates | `templates/**` | — |
| Settings / options | `src/Settings/SettingsRegistry.php` | — |
| REST API | `src/REST/*Controller.php` | — |
| Media handling | `src/Media/MediaHandler.php` | — |
| Display mode / placement | `src/Core/Placement.php` | — |

After changing JS/TS or block sources, run **`npm run build`** (or **`npm run watch`**) from the plugin root.

PHP dev tools: **`npm run composer:install`** (no global Composer required — see [`README.md`](./README.md)).

## PHP load map

```
beplus-advanced-reviews.php
  ├── Constants (BEPLUS_ADVANCED_REVIEWS_*)
  ├── Composer / PSR-4 fallback autoload → src/
  ├── beplus_advanced_reviews_boot() → Plugin::boot()
  └── activation / deactivation hooks → Plugin::activate() / deactivate()
```

**Boot order inside `Plugin::boot()`:**

1. `register_core_services()` — container bindings, REST routes, DB schema check
2. `register_services_from_filter()` — `beplus_advanced_reviews.services`
3. `boot_registered_modules()` — call `register()` on each `AbstractModule`
4. `init` — post types, frontend, block category, textdomain, display mode

## Module registry

| Module | Path | Role |
|--------|------|------|
| `AssetLoader` | `src/Core/AssetLoader.php` | Enqueue admin + frontend + block assets |
| `SettingsRegistry` | `src/Settings/SettingsRegistry.php` | Options, defaults, display mode |
| `BlockRegistry` | `src/Blocks/BlockRegistry.php` | Auto-discover `blocks/*/block.json` |
| `ReviewController` | `src/REST/ReviewController.php` | Public reviews REST (list, submit, filter) |
| `MediaHandler` | `src/Media/MediaHandler.php` | Upload validation, paste support, storage, retrieval |
| `SettingsController` | `src/REST/SettingsController.php` | Admin settings REST |
| `SchemaManager` | `src/DB/SchemaManager.php` | Create / migrate custom DB tables on activation |
| `Placement` | `src/Core/Placement.php` | Display mode logic (keep/replace/custom hook) |

## Database schema

```sql
-- Links uploaded images to a review
CREATE TABLE {prefix}bpar_review_media (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  comment_id    BIGINT UNSIGNED NOT NULL,
  attachment_id BIGINT UNSIGNED NOT NULL,          -- wp_posts (attachment)
  sort_order    TINYINT UNSIGNED NOT NULL DEFAULT 0,
  created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_comment (comment_id)
);
```

`SchemaManager::create_tables()` is called on plugin activation and on `plugins_loaded` when the stored schema version is outdated.

## Gutenberg blocks (`blocks/`)

- **Registration:** `BlockRegistry` scans `blocks/*/block.json` and calls `register_block_type_from_metadata()`.
- **Category:** `beplus-advanced-reviews` (registered in `Plugin::register_block_category()`).
- **Build:** esbuild → `blocks/*/index.js`, `admin/js/settings.js` (see [`README.md`](./README.md)).
- **Blocks:**
  - `advanced-review` — primary block; full review list with image gallery, star distribution, filter bar, sort controls, and submit form. Intended for WooCommerce single product templates.
- **Extension filter:** `beplus_advanced_reviews.blocks`.

### `advanced-review` block — front-end data flow

```
Page load
  └── REST GET /reviews?product_id=…  →  ReviewController::get_reviews()
  ├── REST GET /reviews/distribution?product_id=… → ReviewController::get_star_distribution()
  │     Returns paginated review list + star distribution
  │
  └── view.ts hydrates the block DOM:
        ├── Renders star distribution bar chart
        ├── Renders review list cards
        └── Binds filter bar + sort controls

User applies filter (star / images-only)
  └── Client-side filter in view.ts (no reload for first page)
        └── If next page needed → REST GET /reviews?product_id=…&rating=…&has_images=1&page=…

User submits review
  └── REST POST /reviews  →  ReviewController::create_review()
        ├── Validates nonce + WooCommerce verified purchase (optional setting)
        ├── Creates wp_comment via wp_insert_comment()
        └── Handles image uploads → MediaHandler → bpar_review_media
```

## REST API

- **Namespace:** `beplus-advanced-reviews/v1`

| Method | Route | Auth | Description |
|--------|-------|------|-------------|
| `GET` | `/reviews` | public | List reviews; supports `product_id`, `rating`, `has_images`, `page`, `per_page`, `sort` |
| `GET` | `/reviews/distribution` | public | Star distribution counts for a product |
| `POST` | `/reviews` | logged-in or nonce | Submit a new review with rating and optional images |
| `DELETE` | `/reviews/{id}` | `manage_woocommerce` | Remove a review |
| `GET` | `/settings` | `manage_options` | Retrieve plugin settings |
| `POST` | `/settings` | `manage_options` | Save plugin settings |

- Localize REST URL + nonce via `wp_localize_script` (`bparData` object).
- Rate-limit `POST /reviews` per IP/user to prevent spam (configurable threshold in settings).

## Extensibility hooks

Document all hooks in `src/Core/HookManager.php`:

| Hook | Type | Purpose |
|------|------|---------|
| `beplus_advanced_reviews.services` | filter | Register container services |
| `beplus_advanced_reviews.blocks` | filter | Register third-party blocks |
| `beplus-advanced-reviews/review.query` | filter | Modify review query args |
| `beplus-advanced-reviews/review.results` | filter | Modify review result set |
| `beplus-advanced-reviews/review.submitted` | action | Fires after a review is saved |
| `beplus-advanced-reviews/media.uploaded` | action | Fires after review image is attached |
| `beplus_advanced_reviews_template_paths` | filter | Override template paths |

## Quality checks (from plugin root)

**First-time setup:**

```bash
npm install
npm run composer:install   # NOT `composer install`
```

**Before commit / push:**

| Command | When |
|---------|------|
| `npm run precommit` | Dry-run pre-commit |
| `npm run prepush` | Dry-run pre-push (Composer + CI) |
| `npm run git:push` | Push with prepush checks |

Husky **pre-push** runs: `ensure:composer` → `typecheck` → `lint:php:all` → `build`.

- `npm run build` — compile assets
- `npm run lint:php:all` — PHPStan + CS Fixer (needs `vendor/` from composer:install)
- Manual: activate plugin, drop `advanced-review` block into a single product template, submit a test review with images, verify filter bar, check REST endpoints, confirm admin settings save.

## Security baseline

- Every PHP file: `if ( ! defined( 'ABSPATH' ) ) { exit; }`
- Escape all output; sanitize all input.
- `$wpdb->prepare()` for every raw SQL query.
- REST: explicit `permission_callback` per route.
- Nonce verification for admin forms, AJAX, and review submissions.
- Image uploads: validate MIME type server-side via `wp_check_filetype_and_ext()`; reject executables.
- Strip EXIF data from uploaded images (privacy) using `wp_read_image_metadata` + `imagecreatefromjpeg` pipeline or an equivalent library.

## Accessibility baseline

Target **WCAG 2.1 AA** for all plugin-owned UI: review list, filter bar, submission form, star distribution chart, lightbox, and settings screens.

- **i18n:** All visible and assistive copy uses the `beplus-advanced-reviews` text domain. No hard-coded English in `aria-label` or error text.
- **Icon-only controls:** Add `aria-label`; mark decorative SVGs `aria-hidden="true"`.
- **Focus:** Never remove outlines without a visible `:focus-visible` replacement. Use real buttons, links, headings, lists, and form controls — not clickable generic containers.
- **Reduced motion:** Respect `prefers-reduced-motion: reduce` for transitions, lightboxes, and load-more animations.
- **Forms:** Associate labels with inputs, connect validation errors with `aria-describedby`, and keep error copy clear and actionable.
- **Live updates:** Use `aria-live="polite"` for review count changes, filter results, and submission status messages.
- **Keyboard:** Every control must be reachable and usable by keyboard alone; modals trap focus while open and restore it on close.

## Feature reference docs

| Doc | Purpose |
|-----|---------|
| [`docs/advanced-review-block.md`](./docs/advanced-review-block.md) | Primary feature — Advanced Review block spec (media, filter, star distribution, REST, single product template) |
| [`docs/review-media.md`](./docs/review-media.md) | Upload flow, storage, MIME validation, EXIF stripping, lightbox rendering, clipboard paste |
| [`docs/review-filter-ux.md`](./docs/review-filter-ux.md) | Filter bar UX, DOM contract, sort controls, client-side logic, accessibility |
| [`docs/mcp-setup.md`](./docs/mcp-setup.md) | Connect Cursor MCP to `plugin.local` Site Editor + WordPress Abilities API |
| [`Document Plugin.md`](./Document Plugin.md) | Plugin architecture, naming, directory structure |
