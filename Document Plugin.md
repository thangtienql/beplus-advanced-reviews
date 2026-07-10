# Beplus Advanced Reviews For Woocommerce — Plugin Structure Documentation

> This document defines the architecture standards, naming conventions, and build checklist for the **Beplus Advanced Reviews For Woocommerce** plugin.

---

## 1. Plugin Information

| Item | Value |
|------|-------|
| **Display name** | Beplus Advanced Reviews For Woocommerce |
| **Directory slug** | `beplus-advanced-reviews-for-woocommerce` |
| **Bootstrap file** | `beplus-advanced-reviews-for-woocommerce.php` |
| **Text domain** | `beplus-advanced-reviews-for-woocommerce` |
| **PHP namespace** | `BeplusAdvancedReviewsForWoocommerce` |
| **Global function prefix** | `beplus_advanced_reviews_for_woocommerce_` |
| **Constants prefix** | `BEPLUS_ADVANCED_REVIEWS_` |
| **Hook prefix (legacy WP style)** | `beplus_advanced_reviews_for_woocommerce_` |
| **Hook prefix (new, namespaced)** | `beplus-advanced-reviews-for-woocommerce/` or `beplus_advanced_reviews.` |
| **REST namespace** | `beplus-advanced-reviews-for-woocommerce/v1` |
| **Block category** | `beplus-advanced-reviews-for-woocommerce` |
| **Block name prefix** | `beplus-advanced-reviews-for-woocommerce/` |
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
| **Review card** | Avatar, reviewer name, rating score, content, date, media (images & video) |
| **Review media** | Uploaded media (images/videos) or copy/pasted images attached to a review, displayed via lightbox |
| **Review submission form** | Inline form to write and submit a review with rating and media, including video preview |
| **Load More button** | AJAX "Load More" to fetch the next page of reviews |
| **Filter & Sort** | Filter by star rating, show only reviews with media, sort by date/rating. Also supports hiding reviews below a minimum `rating_threshold`. |

### 2.3 Review Card

Each review card displays:

- **Avatar** (Gravatar or user profile image, if logged in)
- **Reviewer name**
- **Rating score** (star rating)
- **Content** (review text)
- **Review date**
- **Images** (clickable thumbnails, opens lightbox)

### 2.4 Media Support

- Upload images and videos via file input (multi-select)
- **Copy/paste from clipboard** into the review form (images only)
- Uses a swappable `MediaStorageInterface` (backed by standard WordPress Media Library by default) for storage
- Linked via `{wpdb->prefix}bparfw_review_media` table
- Lightbox functionality to view both images and videos on the frontend

### 2.5 Plugin Settings — Display Mode

| Mode | Behavior |
|------|----------|
| **Keep default** | WooCommerce's built-in reviews remain as-is; the block can be placed manually |
| **Replace default** | Completely replaces the standard WooCommerce reviews tab/area with the Advanced Reviews block |

---

## 3. Architecture Overview

This plugin uses a **container-based architecture** — every module registers hooks inside `register()`, with no side effects when files are `require`d.

```
beplus-advanced-reviews-for-woocommerce.php   ← Bootstrap: constants, autoload, activation hooks
        │
        ▼
BeplusAdvancedReviewsForWoocommerce\Core\Plugin    ← Entry point: boot(), activate(), deactivate()
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
beplus-advanced-reviews-for-woocommerce/
├── beplus-advanced-reviews-for-woocommerce.php   # Main plugin file (WordPress reads the header here)
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
│   │   └── Placement.php         # Display mode logic (keep/replace)
│   │
│   ├── Reviews/                  # Domain: review storage / formatting
│   │   ├── ReviewRepository.php
│   │   ├── ReviewQuery.php
│   │   ├── ReviewFormatter.php
│   │   └── ReviewSubmission.php
│   │
│   ├── Media/
│   │   ├── MediaHandler.php
│   │   ├── MediaStorageInterface.php
│   │   └── LocalMediaStorage.php
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
├── includes/                     # Procedural / legacy helpers
│   ├── common.php                # Global helper functions
│   ├── hooks.php                 # Centralized add_action/add_filter
│   └── install.php               # DB tables, default options
│
├── admin/                        # Admin UI (PHP views + JS)
│   ├── js/
│   │   ├── settings.js           # Admin settings UI (hand-authored)
│   └── css/
│       └── admin.scss
│
├── blocks/                       # Gutenberg blocks
│   ├── advanced-review/
│   │   ├── block.json
│   │   ├── edit.tsx
│   │   ├── view.js               # Front-end JS (hand-authored)
│   │   ├── view.asset.php
│   │   ├── index.js              # Block entry (compiled)
│   │   ├── index.asset.php
│   │   ├── render.php
│   │   ├── style.scss            # Frontend + editor styles (source)
│   │   ├── style.css             # Compiled styles
│   │   ├── _variables.scss
│   │   ├── _stars.scss
│   │   ├── _review-card.scss
│   │   ├── _load-more.scss
│   │   ├── _layout.scss
│   │   ├── _form.scss
│   │   ├── _filter-bar.scss
│   │   └── _distribution.scss
│   │
│   └── build/                    # esbuild output (view.js bundle)
│       └── view.js
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
│   └── beplus-advanced-reviews-for-woocommerce.pot
│
└── vendor/                       # Composer autoload (dev)
```

> **Note:** This plugin keeps procedural helpers in `includes/` alongside PSR-4 code in `src/`. Prefer `src/` for new classes; use `includes/` for lightweight helper functions or compatibility wrappers.

---

## 5. Bootstrap File — `beplus-advanced-reviews-for-woocommerce.php`

```php
<?php
/**
 * Plugin Name: Beplus Advanced Reviews For Woocommerce
 * Plugin URI:  https://github.com/thangtienql/beplus-advanced-reviews-for-woocommerce
 * Description: Modern WooCommerce product reviews with image support, star distribution, AJAX filtering, and load more.
 * Version:     1.0.0
 * Author:      Beplus
 * Author URI:  https://beplusthemes.com/
 * Text Domain: beplus-advanced-reviews-for-woocommerce
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package BeplusAdvancedReviewsForWoocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_VERSION', '1.0.0' );
define( 'BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

$autoload = BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_PLUGIN_DIR . 'vendor/autoload.php';
if ( file_exists( $autoload ) ) {
	require_once $autoload;
} else {
	spl_autoload_register(
		function ( string $class_name ) {
			$prefix = 'BeplusAdvancedReviewsForWoocommerce\\';
			if ( strncmp( $class_name, $prefix, strlen( $prefix ) ) !== 0 ) {
				return;
			}

			$file = BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_PLUGIN_DIR
				. 'src/'
				. str_replace( '\\', '/', substr( $class_name, strlen( $prefix ) ) )
				. '.php';

			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
	);
}

require_once BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_PLUGIN_DIR . 'includes/common.php';
require_once BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_PLUGIN_DIR . 'includes/hooks.php';

/**
 * Check if WooCommerce is active.
 *
 * @return bool
 */
function beplus_advanced_reviews_for_woocommerce_is_woocommerce_active() {
	return class_exists( 'WooCommerce' );
}

/**
 * Boot plugin.
 *
 * @return \BeplusAdvancedReviewsForWoocommerce\Core\Plugin|null
 */
function beplus_advanced_reviews_for_woocommerce_boot() {
	static $plugin = null;

	if ( ! beplus_advanced_reviews_for_woocommerce_is_woocommerce_active() ) {
		return null;
	}

	if ( null === $plugin ) {
		$plugin = new \BeplusAdvancedReviewsForWoocommerce\Core\Plugin();
		$plugin->boot();
	}

	return $plugin;
}

add_action( 'plugins_loaded', 'beplus_advanced_reviews_for_woocommerce_init' );

/**
 * Init on plugins_loaded.
 *
 * @return void
 */
function beplus_advanced_reviews_for_woocommerce_init() {
	beplus_advanced_reviews_for_woocommerce_boot();
}

add_action( 'admin_notices', 'beplus_advanced_reviews_for_woocommerce_missing_wc_notice' );

/**
 * Show admin notice when WooCommerce is not active.
 *
 * @return void
 */
function beplus_advanced_reviews_for_woocommerce_missing_wc_notice() {
	if ( beplus_advanced_reviews_for_woocommerce_is_woocommerce_active() ) {
		return;
	}

	printf(
		'<div class="notice notice-error"><p>%s</p></div>',
		esc_html__( 'Beplus Advanced Reviews For Woocommerce requires WooCommerce to be installed and active.', 'beplus-advanced-reviews-for-woocommerce' )
	);
}

register_activation_hook( __FILE__, 'beplus_advanced_reviews_for_woocommerce_activate' );
register_deactivation_hook( __FILE__, 'beplus_advanced_reviews_for_woocommerce_deactivate' );

/**
 * Activation handler.
 *
 * @return void
 */
function beplus_advanced_reviews_for_woocommerce_activate() {
	if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			esc_html__( 'Beplus Advanced Reviews For Woocommerce requires PHP 7.4 or higher.', 'beplus-advanced-reviews-for-woocommerce' ),
			'Plugin Activation Error',
			array( 'back_link' => true )
		);
	}

	if ( ! class_exists( 'WooCommerce' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			esc_html__( 'Beplus Advanced Reviews For Woocommerce requires WooCommerce to be installed and active.', 'beplus-advanced-reviews-for-woocommerce' ),
			'Plugin Activation Error',
			array( 'back_link' => true )
		);
	}

	( new \BeplusAdvancedReviewsForWoocommerce\Core\Plugin() )->activate();
}

/**
 * Deactivation handler.
 *
 * @return void
 */
function beplus_advanced_reviews_for_woocommerce_deactivate() {
	( new \BeplusAdvancedReviewsForWoocommerce\Core\Plugin() )->deactivate();
}
```

---

## 6. Naming Conventions

### 6.1 Constants

| Constant | Purpose |
|----------|---------|
| `BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_VERSION` | Plugin version string |
| `BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_PLUGIN_DIR` | Absolute path to plugin root |
| `BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_PLUGIN_URL` | Plugin URL |
| `BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_PLUGIN_BASENAME` | Relative path from `wp-content/plugins/` |

- Always **UPPER_SNAKE_CASE** with the plugin prefix.

### 6.2 Global functions (procedural)

**Pattern:** `{prefix}_{module}_{action}`

**Examples:**

| Function | Purpose |
|----------|---------|
| `beplus_advanced_reviews_for_woocommerce_boot()` | Boot plugin container |
| `beplus_advanced_reviews_for_woocommerce_init()` | Late init hook |
| `beplus_advanced_reviews_for_woocommerce_activate()` | Activation handler |
| `beplus_advanced_reviews_for_woocommerce_get_settings()` | Read merged settings |
| `beplus_advanced_reviews_for_woocommerce_render_review_card()` | Render a review card |
| `beplus_advanced_reviews_for_woocommerce_get_star_distribution()` | Get star distribution data |

**Rules:**

- Prefix is always `beplus_advanced_reviews_for_woocommerce_`.
- Use action verbs: `get_`, `render_`, `register_`, `process_`, `sanitize_`, `is_`, `has_`.
- Include module name when needed: `beplus_advanced_reviews_for_woocommerce_rebuild_review_cache()`.
- Every public function must have full **PHPDoc** with `@param` and `@return`.

### 6.3 Namespaced functions (`src/Functions/`)

```php
namespace BeplusAdvancedReviewsForWoocommerce\Functions;

function get_settings(): array {
	return function_exists( 'beplus_advanced_reviews_for_woocommerce_get_settings' )
		? beplus_advanced_reviews_for_woocommerce_get_settings()
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
BeplusAdvancedReviewsForWoocommerce\Core\Plugin           → src/Core/Plugin.php
BeplusAdvancedReviewsForWoocommerce\Reviews\ReviewRepository → src/Reviews/ReviewRepository.php
BeplusAdvancedReviewsForWoocommerce\REST\ReviewController  → src/REST/ReviewController.php
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
public const REVIEW_QUERY       = 'beplus-advanced-reviews-for-woocommerce/review.query';
public const REVIEW_RESULTS     = 'beplus-advanced-reviews-for-woocommerce/review.results';
public const REVIEW_SUBMITTED   = 'beplus-advanced-reviews-for-woocommerce/review.submitted';
public const MEDIA_UPLOADED     = 'beplus-advanced-reviews-for-woocommerce/media.uploaded';
public const MEDIA_DELETED      = 'beplus-advanced-reviews-for-woocommerce/media.deleted';
```

**Legacy WordPress style (still used for compatibility hooks):**

```php
do_action( 'beplus_advanced_reviews_for_woocommerce_before_review_list', $args );
apply_filters( 'beplus_advanced_reviews_for_woocommerce_review_card_html', $html, $review );
```

### 6.7 Options and transients

```php
// Options
'beplus_advanced_reviews_for_woocommerce_settings'        // main settings (display mode, etc.)
'beplus_advanced_reviews_for_woocommerce_schema_version'  // schema version tracker

// Transients
'beplus_advanced_reviews_for_woocommerce_review_counts'
'beplus_advanced_reviews_for_woocommerce_media_cache'
```

### 6.8 Database tables

```php
// Prefix: {wpdb->prefix}bparfw_
$wpdb->prefix . 'bparfw_review_media'
```

- Short table prefix `bparfw_` (Beplus Advanced Reviews For Woocommerce).
- Create / migrate tables in `activate()` or via `SchemaManager`.

### 6.9 Script and style handles

```php
'beplus-advanced-reviews-for-woocommerce-admin'
'beplus-advanced-reviews-for-woocommerce-data'       // Localizes bparfwData
'beplus-advanced-reviews-for-woocommerce-block-advanced-review'
```

### 6.10 CSS class prefix

```html
<div class="beplus-advanced-reviews-for-woocommerce beplus-advanced-reviews-for-woocommerce__review-card">
```

- BEM blocks: `beplus-advanced-reviews-for-woocommerce__element--modifier`.

---

## 7. Database Schema

```sql
-- Links uploaded images to a review (WooCommerce comment)
CREATE TABLE {prefix}bparfw_review_media (
  id            BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  comment_id    BIGINT UNSIGNED NOT NULL,          -- wp_comments.comment_ID
  attachment_id BIGINT UNSIGNED NOT NULL,          -- wp_posts (attachment)
  sort_order    TINYINT UNSIGNED NOT NULL DEFAULT 0,
  created_at    DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_comment (comment_id)
);
```

`SchemaManager::create_tables()` is called on plugin activation and on `init` when the stored schema version is outdated.

---

## 8. Writing Classes — Standard Patterns

### 8.1 Required PHP file header

```php
<?php
/**
 * Review Controller — exposes review listing and submission endpoints.
 *
 * @package BeplusAdvancedReviewsForWoocommerce
 * @subpackage Reviews
 */

namespace BeplusAdvancedReviewsForWoocommerce\Reviews;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
```

### 8.2 AbstractModule — base for all modules

```php
namespace BeplusAdvancedReviewsForWoocommerce\Core;

abstract class AbstractModule {

	protected Container $container;
	protected string $version;
	protected string $plugin_dir;
	protected string $plugin_url;

	public function __construct( Container $container ) {
		$this->container  = $container;
		$this->version    = BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_VERSION;
		$this->plugin_dir = BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_PLUGIN_DIR;
		$this->plugin_url = BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_PLUGIN_URL;
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
namespace BeplusAdvancedReviewsForWoocommerce\Core;

use BeplusAdvancedReviewsForWoocommerce\Settings\SettingsRegistry;
use BeplusAdvancedReviewsForWoocommerce\DB\SchemaManager;
use BeplusAdvancedReviewsForWoocommerce\Blocks\BlockRegistry;
use BeplusAdvancedReviewsForWoocommerce\REST\ReviewController;
use BeplusAdvancedReviewsForWoocommerce\REST\SettingsController;
use BeplusAdvancedReviewsForWoocommerce\Media\MediaHandler;
use BeplusAdvancedReviewsForWoocommerce\Media\LocalMediaStorage;
use BeplusAdvancedReviewsForWoocommerce\Media\MediaStorageInterface;

class Plugin {

	private Container $container;

	public function __construct() {
		$this->container = new Container();
	}

	public function boot(): void {
		$this->register_core_services();
		$this->register_services_from_filter();
		$this->boot_registered_modules();

		add_action( 'rest_api_init', array( $this, 'init_rest_controllers' ) );
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_filter( 'block_categories_all', array( $this, 'register_block_category' ) );
	}

	public function init_rest_controllers(): void {
		$review_controller = new ReviewController();
		$review_controller->register_routes();

		$settings_controller = new SettingsController();
		$settings_controller->register_routes();
	}

	public function activate(): void {
		$schema = new SchemaManager( $this->container );
		$schema->create_tables();
		flush_rewrite_rules();
	}

	public function deactivate(): void {
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
namespace BeplusAdvancedReviewsForWoocommerce\Settings;

class SettingsRegistry extends AbstractModule {

	private const OPTION_KEY = 'beplus_advanced_reviews_for_woocommerce_settings';

	private const DEFAULTS = array(
		'display_mode'      => 'keep',
		'load_more_count'   => 10,
		'rating_threshold'  => 0,
		'enable_filter'     => true,
		'enable_sort'       => true,
		'enable_images'     => true,
		'enable_paste'      => true,
		'max_image_size_mb' => 2,
		'enable_videos'     => false,
		'max_video_size_mb' => 20,
	);

	public function register(): void {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function get_all(): array { /* merge defaults + stored */ }
	public function get_display_mode(): string { /* return display_mode */ }
	public function update( array $settings ): bool { /* ... */ }
}
```

### 8.6 REST Controller — Reviews

```php
namespace BeplusAdvancedReviewsForWoocommerce\REST;

class ReviewController extends \WP_REST_Controller {

	public function __construct() {
		$this->namespace = 'beplus-advanced-reviews-for-woocommerce/v1';
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

### 8.7 MediaHandler & MediaStorage

```php
namespace BeplusAdvancedReviewsForWoocommerce\Media;

interface MediaStorageInterface {
	public function store( string $file_path, string $filename );
	public function delete( $storage_id ): bool;
	public function get_url( $storage_id ): ?string;
	public function get_thumbnail_url( $storage_id, string $size = 'thumbnail' ): ?string;
	public function get_mime_type( $storage_id ): ?string;
	public function generate_metadata( $storage_id, string $file_path ): void;
}

class MediaHandler extends AbstractModule {
	public function __construct( Container $container, MediaStorageInterface $storage = null ) { ... }

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
├── view.js         # front-end enhancements (AJAX, load more, filter, paste)
├── render.php      # server-side render
├── style.scss      # frontend + editor styles (source)
└── style.css       # compiled styles
```

**Sample block.json:**

```json
{
	"$schema": "https://schemas.wp.org/trunk/block.json",
	"apiVersion": 3,
	"name": "beplus-advanced-reviews/advanced-review",
	"title": "Advanced Reviews",
	"category": "beplus-advanced-reviews-for-woocommerce",
	"icon": "star-filled",
	"description": "Modern WooCommerce product reviews with images, star distribution, filtering, and load more.",
	"textdomain": "beplus-advanced-reviews-for-woocommerce",
	"attributes": {
		"showDistribution": { "type": "boolean", "default": true },
		"showFilterBar":    { "type": "boolean", "default": true },
		"showSubmitForm":   { "type": "boolean", "default": true },
		"showImages":       { "type": "boolean", "default": true },
		"showAvatar":       { "type": "boolean", "default": true },
		"reviewsPerLoad":   { "type": "number",  "default": 10 },
		"enableLazyLoad":   { "type": "boolean", "default": true }
	},
	"supports": {
		"html": false,
		"align": ["wide", "full"]
	},
	"render": "file:./render.php",
	"editorScript": "file:./index.js",
	"viewScript": "file:./view.js",
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
  └── view.js hydrates the block:
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

- **Namespace:** `beplus-advanced-reviews-for-woocommerce/v1`

| Method | Route | Auth | Description |
|--------|-------|------|-------------|
| `GET` | `/reviews` | public | List reviews; supports `product_id`, `rating`, `has_images`, `page`, `per_page`, `sort` |
| `GET` | `/reviews/distribution` | public | Star distribution counts for a product |
| `POST` | `/reviews` | logged-in or nonce | Submit a new review with rating and optional images |
| `DELETE` | `/reviews/{id}` | `manage_woocommerce` | Remove a review |
| `GET` | `/settings` | `manage_options` | Retrieve plugin settings |
| `POST` | `/settings` | `manage_options` | Save plugin settings |

- Localize REST URL + nonce via `wp_localize_script` (`bparfwData` object).

---

## 12. Assets (JS/CSS)

**AssetLoader** pattern:

- Admin: `admin/js/settings.ts` → compiled via esbuild
- Blocks: localized data script handles are registered in block metadata or via `wp_enqueue_scripts`
- `bparfwData` object is localized on a standalone data script (`beplus-advanced-reviews-for-woocommerce-data`) enqueued on every front-end page

**Localized data:**

```php
wp_localize_script(
	'beplus-advanced-reviews-for-woocommerce-data',
	'bparfwData',
	array(
		'restUrl'         => rest_url( 'beplus-advanced-reviews-for-woocommerce/v1/' ),
		'nonce'           => wp_create_nonce( 'wp_rest' ),
		'maxUploadSize'   => beplus_advanced_reviews_for_woocommerce_get_max_image_size(),
		'allowedTypes'    => array( 'image/jpeg', 'image/png', 'image/webp' ),
		'pasteEnabled'    => true,
		'imagesEnabled'   => true,
		'videosEnabled'   => false,
		'maxVideoSize'    => 20971520,
		'videoTypes'      => array( 'video/mp4', 'video/webm', 'video/ogg' ),
		'i18n'            => array(
			'noReviews'       => __( 'No reviews yet.', 'beplus-advanced-reviews-for-woocommerce' ),
			'loadMore'        => __( 'Load More', 'beplus-advanced-reviews-for-woocommerce' ),
			'submitSuccess'   => __( 'Review submitted!', 'beplus-advanced-reviews-for-woocommerce' ),
			'submitError'     => __( 'Something went wrong.', 'beplus-advanced-reviews-for-woocommerce' ),
			'ratingRequired'  => __( 'Please select a rating.', 'beplus-advanced-reviews-for-woocommerce' ),
			'contentRequired' => __( 'Please write a review.', 'beplus-advanced-reviews-for-woocommerce' ),
			'imageTooLarge'   => __( 'Image must be smaller than %s MB.', 'beplus-advanced-reviews-for-woocommerce' ),
			'videoTooLarge'   => __( 'Video must be smaller than %s MB.', 'beplus-advanced-reviews-for-woocommerce' ),
		),
	)
);
```

**Build commands (package.json):**

```json
{
	"scripts": {
		"build:css": "sass --no-source-map --style=compressed blocks/advanced-review/style.scss:blocks/advanced-review/style.css admin/css/admin.scss:admin/css/admin.css",
		"build": "npm run build:css && node esbuild.config.mjs",
		"watch": "node esbuild.config.mjs --watch"
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
function beplus_advanced_reviews_for_woocommerce_get_template( $template_name, $args = array() ) {
	$paths = apply_filters(
		'beplus_advanced_reviews_for_woocommerce_template_paths',
		array(
			get_stylesheet_directory() . '/beplus-advanced-reviews-for-woocommerce/',
			BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_PLUGIN_DIR . 'templates/',
		)
	);
	// locate + load_template() — extracts $args, includes file
}
```

Theme override: copy a template to `{theme}/beplus-advanced-reviews-for-woocommerce/review-card.php`.

---

## 14. composer.json

```json
{
	"name": "beplus/beplus-advanced-reviews-for-woocommerce",
	"description": "Modern WooCommerce product reviews with image support, AJAX filtering, and load more.",
	"type": "wordpress-plugin",
	"license": "GPL-2.0-or-later",
	"autoload": {
		"psr-4": {
			"BeplusAdvancedReviewsForWoocommerce\\": "src/"
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
| i18n | `__( 'Text', 'beplus-advanced-reviews-for-woocommerce' )`, `_e()`, `esc_html__()` |

---

## 16. Internationalization (i18n)

- Text domain: `beplus-advanced-reviews-for-woocommerce`
- Domain Path: `/languages`
- Load in `Plugin::load_textdomain()`:

```php
load_plugin_textdomain(
	'beplus-advanced-reviews-for-woocommerce',
	false,
	dirname( BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_PLUGIN_BASENAME ) . '/languages'
);
```

- Generate POT: `wp i18n make-pot . languages/beplus-advanced-reviews-for-woocommerce.pot`

---

## 17. Accessibility Baseline

Target **WCAG 2.1 AA** for all plugin-owned UI: review list, filter bar, submission form, star distribution chart, lightbox, and settings screens.

- **i18n:** All visible and assistive copy uses the `beplus-advanced-reviews-for-woocommerce` text domain.
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
| `beplus-advanced-reviews-for-woocommerce/review.query` | filter | Modify review query args |
| `beplus-advanced-reviews-for-woocommerce/review.results` | filter | Modify review result set |
| `beplus-advanced-reviews-for-woocommerce/review.submitted` | action | Fires after a review is saved |
| `beplus-advanced-reviews-for-woocommerce/media.uploaded` | action | Fires after review media is attached |
| `beplus-advanced-reviews-for-woocommerce/media.deleted` | action | Fires after review media is deleted |
| `beplus_advanced_reviews_for_woocommerce_template_paths` | filter | Override template paths |

---

## 19. New Plugin Build Checklist

### Phase 1 — Scaffold
- [ ] Create `beplus-advanced-reviews-for-woocommerce/` directory
- [ ] Write `beplus-advanced-reviews-for-woocommerce.php` with plugin header
- [ ] Define `BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_*` constants
- [ ] Set up `composer.json` + PSR-4 autoload
- [ ] Create `src/Core/Plugin.php`, `Container.php`, `AbstractModule.php`
- [ ] Create `readme.txt`

### Phase 2 — Core modules
- [ ] `AssetLoader` — enqueue admin + frontend
- [ ] `SettingsRegistry` — options + defaults (display mode)
- [ ] `HookManager` — document all hooks
- [ ] `Placement` — display mode logic (keep/replace)
- [ ] `includes/common.php` — global helpers
- [ ] `includes/hooks.php` — wire custom actions

### Phase 3 — Domain (Reviews)
- [ ] `ReviewRepository` + `ReviewQuery` — query WooCommerce comments
- [ ] `ReviewFormatter` — shape review data for API responses
- [ ] `ReviewSubmission` — validate + insert reviews
- [ ] `MediaHandler` — upload validation, paste handler, attachment linking
- [ ] `SchemaManager` — `bparfw_review_media` table
- [ ] REST: `ReviewController`, `SettingsController`

### Phase 4 — UI
- [ ] Admin settings page (TypeScript + REST)
- [ ] Block `advanced-review` (block.json, render.php, edit.tsx, view.js)
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
| `BeplusAdvancedReviewsForWoocommerce\Core\Plugin` | `src/Core/Plugin.php` | Boot, activate, deactivate |
| `BeplusAdvancedReviewsForWoocommerce\Core\Placement` | `src/Core/Placement.php` | Display mode logic |
| `ReviewController` | `src/REST/ReviewController.php` | Review REST API |
| `SettingsController` | `src/REST/SettingsController.php` | Settings REST API |
| `SettingsRegistry` | `src/Settings/SettingsRegistry.php` | Options + defaults |
| `MediaHandler` | `src/Media/MediaHandler.php` | Image uploads, paste, validation |
| `MediaStorageInterface` | `src/Media/MediaStorageInterface.php` | Storage backend contract |
| `LocalMediaStorage` | `src/Media/LocalMediaStorage.php` | WP Media Library backend |
| `SchemaManager` | `src/DB/SchemaManager.php` | Database schema |
| `BlockRegistry` | `src/Blocks/BlockRegistry.php` | Auto-discover blocks |
| `ReviewRepository` | `src/Reviews/ReviewRepository.php` | Review data access |
| `ReviewFormatter` | `src/Reviews/ReviewFormatter.php` | API response formatting |
| `ReviewSubmission` | `src/Reviews/ReviewSubmission.php` | Review creation logic |
| REST namespace | `beplus-advanced-reviews-for-woocommerce/v1` | Public API |
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
