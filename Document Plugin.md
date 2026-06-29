# BePlus Advanced Reviews — Plugin Structure Documentation

> This document defines the architecture standards, naming conventions, and build checklist for the **BePlus Advanced Reviews** plugin.

---

## 1. Plugin Information

| Item | Value |
|------|-------|
| **Display name** | BePlus Advanced Reviews |
| **Directory slug** | `beplus-advanced-reviews` |
| **Bootstrap file** | `beplus-advanced-reviews.php` |
| **Text domain** | `beplus-advanced-reviews` |
| **PHP namespace** | `BePlusAdvancedReviews` |
| **Global function prefix** | `beplus_advanced_reviews_` |
| **Constants prefix** | `BEPLUS_ADVANCED_REVIEWS_` |
| **Hook prefix (legacy WP style)** | `beplus_advanced_reviews_` |
| **Hook prefix (new, namespaced)** | `beplus-advanced-reviews/` or `beplus_advanced_reviews.` |
| **REST namespace** | `beplus-advanced-reviews/v1` |
| **Block category** | `beplus-advanced-reviews` |
| **Block name prefix** | `beplus-advanced-reviews/` |
| **Requires WP** | 6.0+ |
| **Requires PHP** | 7.4+ (8.0+ recommended) |

---

## 2. Features Overview

The plugin upgrades WooCommerce product reviews with a modern, AJAX-driven interface.

### 2.1 Primary Block — `advanced-review`

A drag-and-drop Gutenberg block that renders the full review experience. **Automatically applied to all Single Product pages** on activation. Users can also manually place the block in any template.

### 2.2 Block Output (Frontend)

| Feature | Description |
|---------|-------------|
| **Average rating score** | Aggregated star rating for the product |
| **Total review count** | Total number of approved reviews |
| **Star distribution chart** | Bar chart showing count per star rating (1★–5★) |
| **Review list** | Paginated list of review cards |
| **Review card** | Avatar, reviewer name, rating score, content, date, images |
| **Review images** | Uploaded or copy/pasted images attached to a review |
| **Review submission form** | Inline form to write and submit a review with rating and images |
| **Load More button** | AJAX "Load More" to fetch the next page of reviews |
| **Filter & Sort** | Filter by star rating, show only reviews with images, sort by date/rating |

### 2.3 Review Card

Each review card displays:

- **Avatar** (Gravatar or user profile image, if logged in)
- **Reviewer name**
- **Rating score** (star rating)
- **Content** (review text)
- **Review date**
- **Images** (clickable thumbnails, opens lightbox)

### 2.4 Image Support

- Upload via file input (multi-select)
- **Copy/paste from clipboard** into the review form
- Uses WordPress Media Library for storage
- Linked via `{wpdb->prefix}bpar_review_media` table

### 2.5 Plugin Settings — Display Mode

| Mode | Behavior |
|------|----------|
| **Keep default** | WooCommerce's built-in reviews remain as-is; the block can be placed manually |
| **Replace default** | Completely replaces the standard WooCommerce reviews tab/area with the Advanced Reviews block |
| **Custom hook position** | Inserts Advanced Reviews at a developer-specified hook (`beplus_advanced_reviews_custom_position`) |

---

## 3. Architecture Overview

This plugin uses a **container-based architecture** — every module registers hooks inside `register()`, with no side effects when files are `require`d.

```
beplus-advanced-reviews.php   ← Bootstrap: constants, autoload, activation hooks
        │
        ▼
BePlusAdvancedReviews\Core\Plugin    ← Entry point: boot(), activate(), deactivate()
        │
        ├── Container                 ← DI container (lazy singleton)
        ├── AbstractModule           ← Base class for all modules
        │
        ├── AssetLoader              ← Enqueue JS/CSS
        ├── SettingsRegistry         ← Options + defaults + display mode
        ├── BlockRegistry            ← Auto-discover blocks/
        ├── ReviewController         ← REST API for review listing/submission
        ├── MediaHandler             ← Image validation + storage (upload & paste)
        └── Services                 ← Schema, filters, formatting
```

**Core principles:**

1. **Single entry point** — the `Plugin` class boots the entire plugin.
2. **No side effects on file load** — only declare classes/functions; attach hooks in `register()`.
3. **PSR-4 autoload** for all new code in `src/`.
4. **Prefix everything** — avoid conflicts with WordPress core and other plugins.
5. **Every PHP file** starts with `if ( ! defined( 'ABSPATH' ) ) { exit; }`.

---

## 4. Recommended Directory Structure

```
beplus-advanced-reviews/
├── beplus-advanced-reviews.php   # Main plugin file (WordPress reads the header here)
├── readme.txt                    # WordPress.org readme (if publishing)
├── composer.json                 # PSR-4 autoload + dev dependencies
├── package.json                  # esbuild / TypeScript build
├── Document Plugin.md            # This document
│
├── src/                          # ★ New PHP code — PSR-4 autoload
│   ├── Core/
│   │   ├── Plugin.php            # Main bootstrap
│   │   ├── Container.php         # Service container
│   │   ├── AbstractModule.php    # Base module
│   │   ├── AssetLoader.php       # Enqueue scripts/styles
│   │   ├── HookManager.php       # Constants for hooks/filters
│   │   ├── Placement.php         # Display mode logic (keep/replace/hook)
│   │   └── Compat.php            # Backward compatibility helpers
│   │
│   ├── Reviews/                  # Domain: review storage / formatting
│   │   ├── ReviewRepository.php
│   │   ├── ReviewQuery.php
│   │   ├── ReviewFormatter.php
│   │   └── ReviewSubmission.php
│   │
│   ├── Media/
│   │   └── MediaHandler.php
│   │
│   ├── Settings/
│   │   └── SettingsRegistry.php  # Options + defaults (display mode, etc.)
│   │
│   ├── REST/
│   │   ├── ReviewController.php
│   │   └── SettingsController.php
│   │
│   ├── DB/
│   │   └── SchemaManager.php
│   │
│   ├── Blocks/
│   │   └── BlockRegistry.php
│   │
│   └── Functions/
│       └── helpers.php
│
├── includes/                     # Procedural / legacy (if backward compat is needed)
│   ├── common.php                # Global helper functions
│   ├── hooks.php                 # Centralized add_action/add_filter
│   └── install.php               # DB tables, default options
│
├── admin/                        # Admin UI (PHP views + TypeScript source)
│   ├── js/
│   │   ├── settings.ts           # Admin settings UI
│   │   └── components/
│   └── css/
│       └── admin.scss
│
├── assets/                       # Source assets (before build)
│   ├── js/
│   │   ├── review-list.ts        # Review list, load more, lazy load
│   │   ├── review-form.ts        # Submission form, image paste handler
│   │   ├── review-filter.ts      # Filter bar & sort logic
│   │   └── star-distribution.ts  # Star distribution chart
│   └── css/
│       ├── reviews.scss
│       └── components/
│
├── build/                        # esbuild output (DO NOT edit by hand)
│   ├── admin.js
│   ├── admin.asset.php
│   └── blocks/
│
├── blocks/                       # Gutenberg blocks
│   ├── advanced-review/
│   │   ├── block.json
│   │   ├── edit.tsx
│   │   ├── view.ts
│   │   ├── render.php
│   │   └── style.css
│   └── index.js                  # Blocks build entry
│
├── templates/                    # Frontend PHP templates
│   ├── review-card.php
│   ├── review-list.php
│   ├── review-form.php
│   ├── star-distribution.php
│   └── partials/
│       └── media-item.php
│
├── languages/                    # .pot, .po, .mo
│   └── beplus-advanced-reviews.pot
│
└── vendor/                       # Composer autoload (dev)
```

> **Note:** This plugin keeps procedural helpers in `includes/` alongside PSR-4 code in `src/`. Prefer `src/` for new classes; use `includes/` for lightweight helper functions or compatibility wrappers.

---

## 5. Bootstrap File — `beplus-advanced-reviews.php`

```php
<?php
/**
 * Plugin Name: BePlus Advanced Reviews
 * Plugin URI:  https://beplusthemes.com/
 * Description: Modern WooCommerce product reviews with image support, star distribution, AJAX filtering, and load more.
 * Version:     1.0.0
 * Author:      Beplus
 * Author URI:  https://beplusthemes.com/
 * Text Domain: beplus-advanced-reviews
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package BePlusAdvancedReviews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BEPLUS_ADVANCED_REVIEWS_VERSION', '1.0.0' );
define( 'BEPLUS_ADVANCED_REVIEWS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BEPLUS_ADVANCED_REVIEWS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BEPLUS_ADVANCED_REVIEWS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

$autoload = BEPLUS_ADVANCED_REVIEWS_PLUGIN_DIR . 'vendor/autoload.php';
if ( file_exists( $autoload ) ) {
	require_once $autoload;
} else {
	spl_autoload_register(
		function ( string $class_name ) {
			$prefix = 'BePlusAdvancedReviews\\';
			if ( strncmp( $class_name, $prefix, strlen( $prefix ) ) !== 0 ) {
				return;
			}

			$file = BEPLUS_ADVANCED_REVIEWS_PLUGIN_DIR
				. 'src/'
				. str_replace( '\\', '/', substr( $class_name, strlen( $prefix ) ) )
				. '.php';

			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
	);
}

/**
 * Boot plugin.
 *
 * @return \BePlusAdvancedReviews\Core\Plugin
 */
function beplus_advanced_reviews_boot() {
	static $plugin = null;

	if ( null === $plugin ) {
		$plugin = new \BePlusAdvancedReviews\Core\Plugin();
		$plugin->boot();
	}

	return $plugin;
}

add_action( 'plugins_loaded', 'beplus_advanced_reviews_init' );

/**
 * Init on plugins_loaded.
 *
 * @return void
 */
function beplus_advanced_reviews_init() {
	beplus_advanced_reviews_boot();
}

register_activation_hook( __FILE__, 'beplus_advanced_reviews_activate' );
register_deactivation_hook( __FILE__, 'beplus_advanced_reviews_deactivate' );

/**
 * Activation handler.
 *
 * @return void
 */
function beplus_advanced_reviews_activate() {
	if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			esc_html__( 'BePlus Advanced Reviews requires PHP 7.4 or higher.', 'beplus-advanced-reviews' ),
			'Plugin Activation Error',
			array( 'back_link' => true )
		);
	}

	( new \BePlusAdvancedReviews\Core\Plugin() )->activate();
}

/**
 * Deactivation handler.
 *
 * @return void
 */
function beplus_advanced_reviews_deactivate() {
	( new \BePlusAdvancedReviews\Core\Plugin() )->deactivate();
}
```

---

## 6. Naming Conventions

### 6.1 Constants

| Constant | Purpose |
|----------|---------|
| `BEPLUS_ADVANCED_REVIEWS_VERSION` | Plugin version string |
| `BEPLUS_ADVANCED_REVIEWS_PLUGIN_DIR` | Absolute path to plugin root |
| `BEPLUS_ADVANCED_REVIEWS_PLUGIN_URL` | Plugin URL |
| `BEPLUS_ADVANCED_REVIEWS_PLUGIN_BASENAME` | Relative path from `wp-content/plugins/` |

- Always **UPPER_SNAKE_CASE** with the plugin prefix.

### 6.2 Global functions (procedural)

**Pattern:** `{prefix}_{module}_{action}`

**Examples:**

| Function | Purpose |
|----------|---------|
| `beplus_advanced_reviews_boot()` | Boot plugin container |
| `beplus_advanced_reviews_init()` | Late init hook |
| `beplus_advanced_reviews_activate()` | Activation handler |
| `beplus_advanced_reviews_get_settings()` | Read merged settings |
| `beplus_advanced_reviews_render_review_card()` | Render a review card |
| `beplus_advanced_reviews_get_star_distribution()` | Get star distribution data |

**Rules:**

- Prefix is always `beplus_advanced_reviews_`.
- Use action verbs: `get_`, `render_`, `register_`, `process_`, `sanitize_`, `is_`, `has_`.
- Include module name when needed: `beplus_advanced_reviews_rebuild_review_cache()`.
- Every public function must have full **PHPDoc** with `@param` and `@return`.

### 6.3 Namespaced functions (`src/Functions/`)

```php
namespace BePlusAdvancedReviews\Functions;

function get_settings(): array {
	return function_exists( 'beplus_advanced_reviews_get_settings' )
		? beplus_advanced_reviews_get_settings()
		: array();
}
```

- **camelCase** inside namespaces (PSR-1).
- Global functions remain **snake_case** with prefix.

### 6.4 Class naming

| Type | Convention | Example |
|------|------------|---------|
| Core | PascalCase | `Plugin`, `Container` |
| Abstract base | `Abstract` + name | `AbstractModule` |
| Interface | name + `Interface` | `ReviewRepositoryInterface` |
| Registry | name + `Registry` | `SettingsRegistry`, `BlockRegistry` |
| REST controller | name + `Controller` | `ReviewController` |
| Service | PascalCase | `MediaHandler`, `SchemaManager` |
| Trait | `Has` + name + `Trait` | `HasSettingsTrait` |

**Namespace mapping (PSR-4):**

```
BePlusAdvancedReviews\Core\Plugin           → src/Core/Plugin.php
BePlusAdvancedReviews\Reviews\ReviewRepository → src/Reviews/ReviewRepository.php
BePlusAdvancedReviews\REST\ReviewController  → src/REST/ReviewController.php
```

### 6.5 File naming

| Location | Convention | Example |
|----------|------------|---------|
| `src/` | PascalCase matching class name | `ReviewController.php` |
| `includes/` | `{name}.php` or `class-{name}.php` | `hooks.php`, `common.php` |
| Templates | descriptive kebab-case | `review-card.php` |
| Blocks folder | kebab-case | `advanced-review/block.json` |
| SCSS partial | `_component-name.scss` | `_review-card.scss` |
| TS component | PascalCase.tsx | `ReviewForm.tsx` |
| TS module | kebab-case.ts | `review-filter.ts` |

### 6.6 Hooks, Filters, and Actions

**Modern style (recommended) — dot/slash notation:**

```php
// HookManager.php
public const SERVICES           = 'beplus_advanced_reviews.services';
public const BLOCKS             = 'beplus_advanced_reviews.blocks';
public const REVIEW_QUERY       = 'beplus-advanced-reviews/review.query';
public const REVIEW_RESULTS     = 'beplus-advanced-reviews/review.results';
public const REVIEW_SUBMITTED   = 'beplus-advanced-reviews/review.submitted';
public const MEDIA_UPLOADED     = 'beplus-advanced-reviews/media.uploaded';
public const CUSTOM_POSITION    = 'beplus_advanced_reviews_custom_position';
```

**Legacy WordPress style (still used for compatibility hooks):**

```php
do_action( 'beplus_advanced_reviews_before_review_list', $args );
apply_filters( 'beplus_advanced_reviews_review_card_html', $html, $review );
```

### 6.7 Options and transients

```php
// Options
'beplus_advanced_reviews_settings'        // main settings (display mode, etc.)
'beplus_advanced_reviews_schema_version'  // schema version tracker

// Transients
'beplus_advanced_reviews_review_counts'
'beplus_advanced_reviews_media_cache'
```

### 6.8 Database tables

```php
// Prefix: {wpdb->prefix}bpar_
$wpdb->prefix . 'bpar_review_media'
```

- Short table prefix `bpar_` (BePlus Advanced Reviews).
- Create / migrate tables in `activate()` or via `SchemaManager`.

### 6.9 Script and style handles

```php
'beplus-advanced-reviews-admin'
'beplus-advanced-reviews-frontend'
'beplus-advanced-reviews-block-advanced-review'
```

### 6.10 CSS class prefix

```html
<div class="beplus-advanced-reviews beplus-advanced-reviews__review-card">
```

- BEM blocks: `beplus-advanced-reviews__element--modifier`.

---

## 7. Database Schema

```sql
-- Links uploaded images to a review (WooCommerce comment)
CREATE TABLE {prefix}bpar_review_media (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  comment_id    BIGINT UNSIGNED NOT NULL,          -- wp_comments.comment_ID
  attachment_id BIGINT UNSIGNED NOT NULL,          -- wp_posts (attachment)
  sort_order    TINYINT UNSIGNED NOT NULL DEFAULT 0,
  created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_comment (comment_id)
);
```

`SchemaManager::create_tables()` is called on plugin activation and on `plugins_loaded` when the stored schema version is outdated.

---

## 8. Writing Classes — Standard Patterns

### 8.1 Required PHP file header

```php
<?php
/**
 * Review Controller — exposes review listing and submission endpoints.
 *
 * @package BePlusAdvancedReviews
 * @subpackage Reviews
 */

namespace BePlusAdvancedReviews\Reviews;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
```

### 8.2 AbstractModule — base for all modules

```php
namespace BePlusAdvancedReviews\Core;

abstract class AbstractModule {

	protected Container $container;
	protected string $version;
	protected string $plugin_dir;
	protected string $plugin_url;

	public function __construct( Container $container ) {
		$this->container  = $container;
		$this->version    = BEPLUS_ADVANCED_REVIEWS_VERSION;
		$this->plugin_dir = BEPLUS_ADVANCED_REVIEWS_PLUGIN_DIR;
		$this->plugin_url = BEPLUS_ADVANCED_REVIEWS_PLUGIN_URL;
	}

	/**
	 * Register WordPress hooks. Called ONCE during boot.
	 */
	abstract public function register(): void;
}
```

**Module rules:**

- Constructor receives `Container`.
- All `add_action()` / `add_filter()` calls live inside `register()`.
- Do not call WordPress APIs at file top level (outside `register()`).

### 8.3 Plugin class — boot flow

```php
namespace BePlusAdvancedReviews\Core;

class Plugin {

	private Container $container;

	public function __construct() {
		$this->container = new Container();
	}

	public function boot(): void {
		$this->register_core_services();
		$this->register_services_from_filter();
		$this->boot_registered_modules();

		add_action( 'init', array( $this, 'on_init' ) );
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_filter( 'block_categories_all', array( $this, 'register_block_category' ) );

		// Apply display mode (keep / replace / custom hook)
		add_action( 'init', array( $this, 'apply_display_mode' ) );
	}

	public function on_init(): void {
		$this->init_rest_controllers();
	}

	public function apply_display_mode(): void {
		$mode = $this->container->get( SettingsRegistry::class )->get_display_mode();

		switch ( $mode ) {
			case 'replace':
				// Remove default WooCommerce reviews tab
				add_filter( 'woocommerce_product_tabs', array( Core\Placement::class, 'replace_reviews_tab' ) );
				break;
			case 'custom_hook':
				// Let developer hook into beplus_advanced_reviews_custom_position
				add_action( HookManager::CUSTOM_POSITION, array( Core\Placement::class, 'render_at_custom_hook' ) );
				break;
			case 'keep':
			default:
				// Block is available but no automatic replacement
				break;
		}
	}

	public function activate(): void {
		// Create tables, default options, flush rewrite rules.
		flush_rewrite_rules();
	}

	public function deactivate(): void {
		// Clear cron, flush rewrite rules.
		flush_rewrite_rules();
	}
}
```

### 8.4 Container — dependency injection

The `Container` supports:

- `set( $id, $factory )` — register a factory
- `get( $id )` — lazy-resolve singleton
- `register( array $services )` — bulk register
- Auto-instantiate if not registered: `new $id( $this )`

**Third-party extension filter:**

```php
$services = apply_filters( HookManager::SERVICES, array() );
$this->container->register( $services );
```

### 8.5 SettingsRegistry — Display Mode

```php
namespace BePlusAdvancedReviews\Settings;

class SettingsRegistry extends AbstractModule {

	private const OPTION_KEY = 'beplus_advanced_reviews_settings';

	private const DEFAULTS = array(
		'display_mode'      => 'replace',   // 'keep' | 'replace' | 'custom_hook'
		'enable_images'     => true,
		'enable_paste'      => true,        // clipboard paste support
		'enable_filter'     => true,
		'enable_sort'       => true,
		'load_more_count'   => 10,          // reviews per "load more"
		'rating_threshold'  => 0,           // minimum rating to display (0 = show all)
	);

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
	}

	public function get_all(): array { /* merge defaults + stored */ }
	public function get_display_mode(): string { /* return display_mode */ }
	public function update( array $settings ): bool { /* ... */ }
}
```

### 8.6 REST Controller — Reviews

```php
namespace BePlusAdvancedReviews\REST;

class ReviewController extends \WP_REST_Controller {

	public function __construct() {
		$this->namespace = 'beplus-advanced-reviews/v1';
		$this->rest_base = 'reviews';
	}

	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_items' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_collection_params(),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'create_item' ),
					'permission_callback' => array( $this, 'can_create_item' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/distribution',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_star_distribution' ),
					'permission_callback' => '__return_true',
					'args'                => array(
						'product_id' => array(
							'required'          => true,
							'validate_callback' => function ( $param ) {
								return absint( $param ) > 0;
							},
						),
					),
				),
			)
		);
	}
}
```

### 8.7 MediaHandler — Upload & Paste Support

```php
namespace BePlusAdvancedReviews\Media;

class MediaHandler extends AbstractModule {

	/**
	 * Handle uploaded files from form $_FILES.
	 *
	 * @param int   $comment_id
	 * @param array $files  $_FILES array
	 * @return array attachment IDs
	 */
	public function upload_files( int $comment_id, array $files ): array { /* ... */ }

	/**
	 * Handle a pasted/base64 image from clipboard.
	 *
	 * @param int    $comment_id
	 * @param string $base64_data  Data URL from clipboard paste
	 * @return int|null  attachment ID or null on failure
	 */
	public function upload_pasted_image( int $comment_id, string $base64_data ): ?int { /* ... */ }

	/**
	 * Get media attached to a review.
	 *
	 * @param int $comment_id
	 * @return array List of attachment data (id, url, thumbnail_url)
	 */
	public function get_review_media( int $comment_id ): array { /* ... */ }
}
```

---

## 9. Gutenberg Block — `advanced-review`

Block structure:

```
blocks/advanced-review/
├── block.json      # metadata, attributes, render callback
├── edit.tsx        # editor UI (placeholder preview)
├── view.ts         # front-end enhancements (AJAX, load more, filter, paste)
├── render.php      # server-side render
└── style.css       # frontend + editor styles
```

**Sample block.json:**

```json
{
	"$schema": "https://schemas.wp.org/trunk/block.json",
	"apiVersion": 3,
	"name": "beplus-advanced-reviews/advanced-review",
	"title": "Advanced Reviews",
	"category": "beplus-advanced-reviews",
	"icon": "star-filled",
	"description": "Modern WooCommerce product reviews with images, star distribution, filtering, and load more.",
	"attributes": {
		"showDistribution": { "type": "boolean", "default": true },
		"showFilterBar":    { "type": "boolean", "default": true },
		"showSubmitForm":   { "type": "boolean", "default": true },
		"showImages":       { "type": "boolean", "default": true },
		"showAvatar":       { "type": "boolean", "default": true },
		"reviewsPerLoad":   { "type": "number",  "default": 10 },
		"enableLazyLoad":   { "type": "boolean", "default": true }
	},
	"render": "file:./render.php",
	"editorScript": "beplus-advanced-reviews-block-advanced-review",
	"viewScript": "file:./view.ts",
	"style": "file:./style.css"
}
```

**BlockRegistry** auto-scans `blocks/*/block.json` and calls `register_block_type_from_metadata()`.

Extension filter:

```php
apply_filters( 'beplus_advanced_reviews.blocks', array() );
```

---

## 10. Front-End Data Flow

```
Page load
  └── REST GET /reviews?product_id=…        →  ReviewController::get_items()
  ├── REST GET /reviews/distribution?product_id=… → ReviewController::get_star_distribution()
  │     Returns initial review page + star distribution
  │
  └── view.ts hydrates the block:
        ├── Renders star distribution bar chart
        ├── Renders review list cards
        └── Binds filter bar + sort controls

User clicks "Load More"
  └── REST GET /reviews?product_id=…&page=2  →  Appends next page

User applies filter (star rating / has images)
  └── REST GET /reviews?product_id=…&rating=5&has_images=1 →  Replaces list

User submits review
  └── REST POST /reviews  →  ReviewController::create_item()
        ├── Validates nonce
        ├── Creates wp_comment via wp_insert_comment()
        ├── Handles image uploads → MediaHandler
        └── Handles pasted images → MediaHandler::upload_pasted_image()
```

---

## 11. REST API

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

---

## 12. Assets (JS/CSS)

**AssetLoader** pattern:

- Admin: `admin/js/settings.ts` → compiled assets
- Frontend: `assets/js/review-list.ts`, `review-form.ts`, `review-filter.ts`, `star-distribution.ts`
- Blocks: `enqueue_block_assets` hook or block metadata asset handles

**Localized data:**

```php
wp_localize_script(
	'beplus-advanced-reviews-frontend',
	'bparData',
	array(
		'restUrl'         => rest_url( 'beplus-advanced-reviews/v1/' ),
		'nonce'           => wp_create_nonce( 'wp_rest' ),
		'maxUploadSize'   => wp_max_upload_size(),
		'allowedTypes'    => array( 'image/jpeg', 'image/png', 'image/webp' ),
		'pasteEnabled'    => true,
		'i18n'            => array(
			'noReviews'        => __( 'No reviews yet.', 'beplus-advanced-reviews' ),
			'loadMore'         => __( 'Load More', 'beplus-advanced-reviews' ),
			'submitSuccess'    => __( 'Review submitted!', 'beplus-advanced-reviews' ),
			'submitError'      => __( 'Something went wrong.', 'beplus-advanced-reviews' ),
		),
	)
);
```

**Build commands (package.json):**

```json
{
	"scripts": {
		"build": "esbuild",
		"watch": "esbuild --watch",
		"lint:js": "eslint .",
		"lint:css": "stylelint \"**/*.css\""
	}
}
```

---

## 13. Templates

```
templates/
├── review-card.php
├── review-list.php
├── review-form.php
├── star-distribution.php
└── partials/
    └── media-item.php
```

**Load template:**

```php
function beplus_advanced_reviews_get_template( $template_name, $args = array() ) {
	$paths = apply_filters(
		'beplus_advanced_reviews_template_paths',
		array(
			get_stylesheet_directory() . '/beplus-advanced-reviews/',
			BEPLUS_ADVANCED_REVIEWS_PLUGIN_DIR . 'templates/',
		)
	);
	// locate + load_template()
}
```

Theme override: copy a template to `{theme}/beplus-advanced-reviews/review-card.php`.

---

## 14. composer.json

```json
{
	"name": "beplus/beplus-advanced-reviews",
	"description": "Modern WooCommerce product reviews with image support, AJAX filtering, and load more.",
	"type": "wordpress-plugin",
	"license": "GPL-2.0-or-later",
	"autoload": {
		"psr-4": {
			"BePlusAdvancedReviews\\": "src/"
		}
	},
	"require": {
		"php": ">=7.4"
	},
	"require-dev": {
		"phpcompatibility/phpcompatibility-wp": "*",
		"wp-coding-standards/wpcs": "*"
	}
}
```

---

## 15. Security and WordPress Coding Standards

Every file must follow:

| Rule | Implementation |
|------|----------------|
| Direct access | `if ( ! defined( 'ABSPATH' ) ) { exit; }` |
| Output | `esc_html()`, `esc_attr()`, `esc_url()`, `wp_kses_post()` |
| Input | `sanitize_text_field()`, `absint()`, `wp_unslash()` |
| Nonce | `wp_verify_nonce()` for forms/AJAX |
| Capability | `current_user_can( 'manage_options' )` for admin |
| REST | explicit `permission_callback`; do not use `__return_true` for write endpoints |
| SQL | `$wpdb->prepare()` |
| i18n | `__( 'Text', 'beplus-advanced-reviews' )`, `_e()`, `esc_html__()` |

---

## 16. Internationalization (i18n)

- Text domain: `beplus-advanced-reviews`
- Domain Path: `/languages`
- Load in `Plugin::load_textdomain()`:

```php
load_plugin_textdomain(
	'beplus-advanced-reviews',
	false,
	dirname( BEPLUS_ADVANCED_REVIEWS_PLUGIN_BASENAME ) . '/languages'
);
```

- Generate POT: `wp i18n make-pot . languages/beplus-advanced-reviews.pot`

---

## 17. Accessibility Baseline

Target **WCAG 2.1 AA** for all plugin-owned UI: review list, filter bar, submission form, star distribution chart, lightbox, and settings screens.

- **i18n:** All visible and assistive copy uses the `beplus-advanced-reviews` text domain.
- **Icon-only controls:** Add `aria-label`; mark decorative SVGs `aria-hidden="true"`.
- **Focus:** Never remove outlines without a visible `:focus-visible` replacement. Use real buttons, links, headings, lists, and form controls.
- **Reduced motion:** Respect `prefers-reduced-motion: reduce` for transitions, lightboxes, and load-more animations.
- **Forms:** Associate labels with inputs, connect validation errors with `aria-describedby`.
- **Live updates:** Use `aria-live="polite"` for review count changes, filter results, and submission status.
- **Keyboard:** Every control must be reachable and usable by keyboard alone.

---

## 18. Extensibility Hooks

| Hook | Type | Purpose |
|------|------|---------|
| `beplus_advanced_reviews.services` | filter | Register container services |
| `beplus_advanced_reviews.blocks` | filter | Register third-party blocks |
| `beplus-advanced-reviews/review.query` | filter | Modify review query args |
| `beplus-advanced-reviews/review.results` | filter | Modify review result set |
| `beplus-advanced-reviews/review.submitted` | action | Fires after a review is saved |
| `beplus-advanced-reviews/media.uploaded` | action | Fires after review image is attached |
| `beplus_advanced_reviews_custom_position` | action | Custom hook position for display mode |
| `beplus_advanced_reviews_template_paths` | filter | Override template paths |

---

## 19. New Plugin Build Checklist

### Phase 1 — Scaffold
- [ ] Create `beplus-advanced-reviews/` directory
- [ ] Write `beplus-advanced-reviews.php` with plugin header
- [ ] Define `BEPLUS_ADVANCED_REVIEWS_*` constants
- [ ] Set up `composer.json` + PSR-4 autoload
- [ ] Create `src/Core/Plugin.php`, `Container.php`, `AbstractModule.php`
- [ ] Create `readme.txt`

### Phase 2 — Core modules
- [ ] `AssetLoader` — enqueue admin + frontend
- [ ] `SettingsRegistry` — options + defaults (display mode)
- [ ] `HookManager` — document all hooks
- [ ] `Placement` — display mode logic (keep/replace/custom hook)
- [ ] `includes/common.php` — global helpers
- [ ] `includes/hooks.php` — wire custom actions

### Phase 3 — Domain (Reviews)
- [ ] `ReviewRepository` + `ReviewQuery` — query WooCommerce comments
- [ ] `ReviewFormatter` — shape review data for API responses
- [ ] `ReviewSubmission` — validate + insert reviews
- [ ] `MediaHandler` — upload validation, paste handler, attachment linking
- [ ] `SchemaManager` — `bpar_review_media` table
- [ ] REST: `ReviewController`, `SettingsController`

### Phase 4 — UI
- [ ] Admin settings page (TypeScript + REST)
- [ ] Block `advanced-review` (block.json, render.php, edit.tsx, view.ts)
- [ ] Review list template + Load More
- [ ] Review card template (avatar, name, rating, content, date, images)
- [ ] Star distribution chart (bar chart)
- [ ] Review submission form + image paste handler
- [ ] Filter bar + sort controls
- [ ] Lightbox for review images
- [ ] `package.json` + esbuild build

### Phase 5 — Polish
- [ ] Activation: DB tables, default settings
- [ ] Deactivation: clean up
- [ ] `uninstall.php`: remove options/tables (opt-in)
- [ ] PHPCS / WPCS lint
- [ ] i18n POT file
- [ ] Admin notices (first activation)
- [ ] Extensibility filters documented

---

## 20. Core Class Map

| Class | Path | Role |
|-------|------|------|
| `BePlusAdvancedReviews\Core\Plugin` | `src/Core/Plugin.php` | Boot, activate, deactivate |
| `BePlusAdvancedReviews\Core\Placement` | `src/Core/Placement.php` | Display mode logic |
| `ReviewController` | `src/REST/ReviewController.php` | Review REST API |
| `SettingsController` | `src/REST/SettingsController.php` | Settings REST API |
| `SettingsRegistry` | `src/Settings/SettingsRegistry.php` | Options + defaults |
| `MediaHandler` | `src/Media/MediaHandler.php` | Image uploads, paste, validation |
| `SchemaManager` | `src/DB/SchemaManager.php` | Database schema |
| `BlockRegistry` | `src/Blocks/BlockRegistry.php` | Auto-discover blocks |
| `ReviewRepository` | `src/Reviews/ReviewRepository.php` | Review data access |
| `ReviewFormatter` | `src/Reviews/ReviewFormatter.php` | API response formatting |
| `ReviewSubmission` | `src/Reviews/ReviewSubmission.php` | Review creation logic |
| REST namespace | `beplus-advanced-reviews/v1` | Public API |
| Primary block | `blocks/advanced-review/` | Advanced Review block |

---

## 21. Third-Party Extension Example

```php
add_filter( 'beplus_advanced_reviews.services', function ( $services ) {
	$services[ \MyPlugin\CustomReviewFormatter::class ] = function ( $c ) {
		return new \MyPlugin\CustomReviewFormatter( $c );
	};

	return $services;
} );

add_filter( 'beplus_advanced_reviews.blocks', function ( $blocks ) {
	$blocks[] = 'my-plugin/custom-review-widget';
	return $blocks;
} );
```

---

*This document is the blueprint. Update it as the plugin grows with new modules.*
