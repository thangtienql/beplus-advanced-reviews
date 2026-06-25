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

## 2. Architecture Overview

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
        ├── SettingsRegistry         ← Options + defaults
        ├── CriteriaRegistry         ← Rating criteria definitions + weights
        ├── BlockRegistry            ← Auto-discover blocks/
        ├── ReviewController         ← REST API for review listing/submission
        ├── MediaHandler             ← Media validation + storage
        └── Services                 ← Schema, filters, formatting, notifications
```

**Core principles:**

1. **Single entry point** — the `Plugin` class boots the entire plugin.
2. **No side effects on file load** — only declare classes/functions; attach hooks in `register()`.
3. **PSR-4 autoload** for all new code in `src/`.
4. **Prefix everything** — avoid conflicts with WordPress core and other plugins.
5. **Every PHP file** starts with `if ( ! defined( 'ABSPATH' ) ) { exit; }`.

---

## 3. Recommended Directory Structure

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
│   │   └── Compat.php            # Backward compatibility helpers
│   │
│   ├── Reviews/                  # Domain: review storage / formatting
│   │   ├── ReviewController.php
│   │   ├── ReviewRepository.php
│   │   ├── ReviewQuery.php
│   │   ├── ReviewFormatter.php
│   │   └── ReviewSubmission.php
│   │
│   ├── Media/
│   │   └── MediaHandler.php
│   │
│   ├── Settings/
│   │   ├── SettingsRegistry.php
│   │   └── CriteriaRegistry.php
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
│   └── css/
│
├── build/                        # esbuild output (DO NOT edit by hand)
│   ├── admin.js
│   ├── admin.css
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
│   ├── review-form.php
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

## 4. Bootstrap File — `beplus-advanced-reviews.php`

```php
<?php
/**
 * Plugin Name: BePlus Advanced Reviews
 * Plugin URI:  https://beplusthemes.com/
 * Description: Advanced WooCommerce product reviews with media attachments, multi-criteria ratings, and smart filtering.
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

## 5. Naming Conventions

### 5.1 Constants

| Constant | Purpose |
|----------|---------|
| `BEPLUS_ADVANCED_REVIEWS_VERSION` | Plugin version string |
| `BEPLUS_ADVANCED_REVIEWS_PLUGIN_DIR` | Absolute path to plugin root |
| `BEPLUS_ADVANCED_REVIEWS_PLUGIN_URL` | Plugin URL |
| `BEPLUS_ADVANCED_REVIEWS_PLUGIN_BASENAME` | Relative path from `wp-content/plugins/` |

- Always **UPPER_SNAKE_CASE** with the plugin prefix.

### 5.2 Global functions (procedural)

**Pattern:** `{prefix}_{module}_{action}`

**Examples:**

| Function | Purpose |
|----------|---------|
| `beplus_advanced_reviews_boot()` | Boot plugin container |
| `beplus_advanced_reviews_init()` | Late init hook |
| `beplus_advanced_reviews_activate()` | Activation handler |
| `beplus_advanced_reviews_get_settings()` | Read merged settings |
| `beplus_advanced_reviews_sanitize_array()` | Recursive array sanitize |
| `beplus_advanced_reviews_render_review_card()` | Render a review card |

**Rules:**

- Prefix is always `beplus_advanced_reviews_`.
- Use action verbs: `get_`, `render_`, `register_`, `process_`, `sanitize_`, `is_`, `has_`.
- Include module name when needed: `beplus_advanced_reviews_rebuild_review_cache()`.
- Every public function must have full **PHPDoc** with `@param` and `@return`.

### 5.3 Namespaced functions (`src/Functions/`)

Optional namespaced wrappers live in `src/Functions/`:

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

### 5.4 Class naming

| Type | Convention | Example |
|------|------------|---------|
| Core | PascalCase | `Plugin`, `Container` |
| Abstract base | `Abstract` + name | `AbstractModule`, `AbstractProvider` |
| Interface | name + `Interface` | `ReviewRepositoryInterface` |
| Registry | name + `Registry` | `SettingsRegistry`, `CriteriaRegistry` |
| REST controller | name + `Controller` | `ReviewController` |
| Service | name + `Service` | `MediaHandler`, `SchemaManager` |
| Trait | `Has` + name + `Trait` | `HasSettingsTrait` |

**Namespace mapping (PSR-4):**

```
BePlusAdvancedReviews\Core\Plugin           → src/Core/Plugin.php
BePlusAdvancedReviews\Reviews\ReviewController → src/Reviews/ReviewController.php
BePlusAdvancedReviews\REST\ReviewController  → src/REST/ReviewController.php
```

### 5.5 File naming

| Location | Convention | Example |
|----------|------------|---------|
| `src/` | PascalCase matching class name | `ReviewController.php` |
| `includes/` legacy | `class-{name}.php` or `{name}.php` | `hooks.php`, `common.php` |
| Templates | descriptive kebab-case | `review-card.php` |
| Blocks folder | kebab-case | `advanced-review/block.json` |
| SCSS partial | `_component-name.scss` | `_review-card.scss` |
| TS component | PascalCase.tsx | `ReviewForm.tsx` |
| TS module | kebab-case.ts | `review-filter.ts` |

### 5.6 Hooks, Filters, and Actions

The plugin uses **two hook naming styles** — prefer modern namespaced-style constants for new code:

**Modern style (recommended) — dot/slash notation:**

```php
// HookManager.php
public const SERVICES           = 'beplus_advanced_reviews.services';
public const BLOCKS             = 'beplus_advanced_reviews.blocks';
public const CRITERIA           = 'beplus-advanced-reviews/criteria';
public const REVIEW_QUERY       = 'beplus-advanced-reviews/review.query';
public const REVIEW_RESULTS     = 'beplus-advanced-reviews/review.results';
public const REVIEW_SUBMITTED   = 'beplus-advanced-reviews/review.submitted';
public const MEDIA_UPLOADED     = 'beplus-advanced-reviews/media.uploaded';
```

**Legacy WordPress style (still used for compatibility hooks):**

```php
do_action( 'beplus_advanced_reviews_before_review_list', $args );
apply_filters( 'beplus_advanced_reviews_review_card_html', $html, $review );
```

**Custom action hooks (domain events):**

```php
do_action( HookManager::REVIEW_SUBMITTED, $review_id, $comment_id );
```

### 5.7 Options and transients

```php
// Options
'beplus_advanced_reviews_settings'        // main settings
'beplus_advanced_reviews_schema_version'  // schema version tracker
'beplus_advanced_reviews_criteria'        // criteria registry override

// Transients
'beplus_advanced_reviews_review_counts'
'beplus_advanced_reviews_media_cache'
```

### 5.8 Database tables

```php
// Prefix: {wpdb->prefix}bpar_
$wpdb->prefix . 'bpar_criteria_scores'
$wpdb->prefix . 'bpar_review_media'
```

- Short table prefix `bpar_` (BePlus Advanced Reviews).
- Create / migrate tables in `activate()` or via `SchemaManager`.

### 5.9 Script and style handles

```php
'beplus-advanced-reviews-admin'
'beplus-advanced-reviews-frontend'
'beplus-advanced-reviews-block-advanced-review'
```

### 5.10 CSS class prefix

```html
<div class="beplus-advanced-reviews beplus-advanced-reviews__review-card">
```

- BEM blocks: `beplus-advanced-reviews__element--modifier`.

---

## 6. Writing Classes — Standard Patterns

### 6.1 Required PHP file header

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

### 6.2 AbstractModule — base for all modules

Standard module base:

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

### 6.3 Plugin class — boot flow

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
	}

	public function on_init(): void {
		$this->init_frontend();
		$this->init_rest_controllers();
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

### 6.4 Container — dependency injection

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

### 6.5 Review repository / query pattern

```php
namespace BePlusAdvancedReviews\Reviews;

abstract class ReviewRepository {

	abstract public function find_by_product_id( int $product_id, array $args = array() ): array;
	abstract public function save_criteria_scores( int $comment_id, array $scores ): void;
	abstract public function attach_media( int $comment_id, array $attachment_ids ): void;
}
```

### 6.6 REST Controller

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
	}
}
```

### 6.7 SettingsRegistry

```php
namespace BePlusAdvancedReviews\Settings;

class SettingsRegistry extends AbstractModule {

	private const OPTION_KEY = 'beplus_advanced_reviews_settings';

	private const DEFAULTS = array(
		'general' => array(
			'enable_media_uploads' => true,
			'enable_filters'       => true,
		),
		'criteria' => array(
			'material_quality'    => 1,
			'matches_description' => 1,
			'delivery_speed'      => 1,
		),
	);

	public function register(): void {
		add_action( 'admin_init', array( $this, 'maybe_migrate_settings' ) );
	}

	public function get_all(): array { /* merge defaults + stored */ }
	public function get_group( string $group ): array { /* ... */ }
	public function update( array $settings ): bool { /* ... */ }
}
```

### 6.8 CriteriaRegistry

```php
namespace BePlusAdvancedReviews\Settings;

class CriteriaRegistry extends AbstractModule {

	public function get_default_criteria(): array {
		return array(
			'material_quality' => array(
				'label'  => __( 'Material Quality', 'beplus-advanced-reviews' ),
				'weight' => 1,
			),
		);
	}
}
```

---

## 7. Gutenberg Blocks

Block structure:

```
blocks/advanced-review/
├── block.json      # metadata, attributes, render callback
├── edit.tsx        # editor UI
├── view.ts         # front-end enhancements
├── render.php      # server-side render
└── style.css       # frontend + editor styles
```

**Sample block.json:**

```json
{
	"$schema": "https://schemas.wp.org/trunk/block.json",
	"apiVersion": 3,
	"name": "beplus-advanced-reviews/advanced-review",
	"title": "Advanced Review",
	"category": "beplus-advanced-reviews",
	"icon": "star-filled",
	"description": "Advanced WooCommerce product reviews with media, criteria, and filtering.",
	"attributes": {
		"showFilterBar": { "type": "boolean", "default": true },
		"showSubmitForm": { "type": "boolean", "default": true }
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

## 8. Assets (JS/CSS)

**AssetLoader** pattern:

- Admin: `admin/js/settings.ts` → compiled assets
- Frontend: block `view.ts` and plugin front-end assets
- Blocks: `enqueue_block_assets` hook or block metadata asset handles
- Legacy fallback: `assets/js/*.js` if the plugin supports it

**Localized data:**

```php
wp_localize_script(
	'beplus-advanced-reviews-frontend',
	'bparData',
	array(
		'restUrl' => rest_url( 'beplus-advanced-reviews/v1/' ),
		'nonce'   => wp_create_nonce( 'wp_rest' ),
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

## 9. Templates

```
templates/
├── review-card.php
├── review-form.php
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

## 10. composer.json

```json
{
	"name": "beplus/beplus-advanced-reviews",
	"description": "BePlus Advanced Reviews for WordPress and WooCommerce",
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

## 11. Security and WordPress Coding Standards

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

## 12. Internationalization (i18n)

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

## 13. Cron Jobs

Use cron sparingly for cache refresh, cleanup, or scheduled review tasks.

```php
// Register
if ( ! wp_next_scheduled( HookManager::REVIEW_CACHE_REFRESH ) ) {
	wp_schedule_event( time(), 'hourly', HookManager::REVIEW_CACHE_REFRESH );
}

// Handler
add_action( HookManager::REVIEW_CACHE_REFRESH, array( $review_service, 'refresh_cache' ) );

// Deactivate: wp_clear_scheduled_hook( HookManager::REVIEW_CACHE_REFRESH );
```

---

## 14. New Plugin Build Checklist

### Phase 1 — Scaffold

- [ ] Create `beplus-advanced-reviews/` directory
- [ ] Write `beplus-advanced-reviews.php` with plugin header
- [ ] Define `BEPLUS_ADVANCED_REVIEWS_*` constants
- [ ] Set up `composer.json` + PSR-4 autoload
- [ ] Create `src/Core/Plugin.php`, `Container.php`, `AbstractModule.php`
- [ ] Create `readme.txt`

### Phase 2 — Core modules

- [ ] `AssetLoader` — enqueue admin + frontend
- [ ] `SettingsRegistry` — options + defaults
- [ ] `CriteriaRegistry` — rating criteria definitions
- [ ] `HookManager` — document all hooks
- [ ] `includes/common.php` — global helpers
- [ ] `includes/hooks.php` — wire custom actions

### Phase 3 — Domain (Advanced Reviews)

- [ ] `ReviewController` — list / submit / delete reviews
- [ ] `ReviewRepository` + query helpers
- [ ] `MediaHandler` — upload validation and attachment linking
- [ ] `SchemaManager` — DB tables and migrations
- [ ] REST: `ReviewController`, `SettingsController`
- [ ] Filter and criteria result formatting

### Phase 4 — UI

- [ ] Admin settings (TypeScript + REST)
- [ ] Block `advanced-review`
- [ ] Review submission form and review list templates
- [ ] Frontend filter bar + media preview behavior
- [ ] `package.json` + esbuild build

### Phase 5 — Polish

- [ ] Activation: DB tables, default settings, flush rewrites if needed
- [ ] Deactivation: clear cron
- [ ] `uninstall.php`: remove options/tables (if user opts in)
- [ ] PHPCS / WPCS lint
- [ ] i18n POT file
- [ ] Admin notices (first activation)
- [ ] Extensibility filters documented

---

## 15. Core class map

| Class | Path | Role |
|-------|------|------|
| `BePlusAdvancedReviews\Core\Plugin` | `src/Core/Plugin.php` | Boot, activate, deactivate |
| `ReviewController` | `src/REST/ReviewController.php` | Review REST API |
| `SettingsRegistry` | `src/Settings/SettingsRegistry.php` | Options + defaults |
| `CriteriaRegistry` | `src/Settings/CriteriaRegistry.php` | Criteria definitions |
| `MediaHandler` | `src/Media/MediaHandler.php` | Media uploads and validation |
| `SchemaManager` | `src/DB/SchemaManager.php` | Database schema |
| `BlockRegistry` | `src/Blocks/BlockRegistry.php` | Auto-discover blocks |
| REST namespace | `beplus-advanced-reviews/v1` | Public API |
| Services filter | `beplus_advanced_reviews.services` | Container extensions |
| Review submitted action | `beplus-advanced-reviews/review.submitted` | After review save |
| Primary block | `blocks/advanced-review/` | Advanced Review block |

---

## 16. Third-Party Extension Example

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

## 17. Advanced Review Block (primary feature)

Before building the main plugin feature, read:

**[`docs/advanced-review-block.md`](./docs/advanced-review-block.md)**

That document specifies the `beplus-advanced-reviews/advanced-review` block: review list, filter bar, media attachments, criteria breakdown, and submit form within WooCommerce single product contexts.

---

## 18. Review Media and Criteria docs

Before implementing media uploads or rating calculations, read:

- **[`docs/review-media.md`](./docs/review-media.md)**
- **[`docs/multi-criteria-rating.md`](./docs/multi-criteria-rating.md)**
- **[`docs/review-filter-ux.md`](./docs/review-filter-ux.md)**

These documents cover media validation, storage, criteria weights, result shaping, and accessible filter behavior.

---

## 19. Internal reference files

When implementing, read these plugin files directly:

| File | Purpose |
|------|---------|
| `beplus-advanced-reviews.php` | Bootstrap pattern |
| `src/Core/Plugin.php` | Boot flow, activate/deactivate |
| `src/Core/Container.php` | DI container |
| `src/Core/AbstractModule.php` | Module base |
| `src/Core/AssetLoader.php` | Asset enqueue |
| `src/Settings/SettingsRegistry.php` | Settings pattern |
| `src/Settings/CriteriaRegistry.php` | Criteria definitions |
| `src/REST/ReviewController.php` | Review REST API |
| `src/Media/MediaHandler.php` | Media validation and storage |
| `src/DB/SchemaManager.php` | Schema migrations |
| `blocks/advanced-review/block.json` | Primary block metadata |
| `blocks/advanced-review/view.ts` | Front-end review enhancement JS |

---

*This document is the initial blueprint. Update it as the plugin grows with new modules.*
