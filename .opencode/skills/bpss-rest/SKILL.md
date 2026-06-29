---
name: bpss-rest
description: Use when creating or editing REST API controllers, routes, endpoints, or API responses under src/REST/. Covers namespace, permissions, sanitization, and response shapes for the beplus-advanced-reviews/v1 API.
---

# BePlus Advanced Reviews — REST API

## Namespace

- Base: `beplus-advanced-reviews/v1`
- Controllers live under `src/REST/` and extend `WP_REST_Controller` when appropriate.

## Route conventions

| Route | Methods | Permission | Notes |
|-------|---------|------------|-------|
| `/reviews` | GET | public read | Return product review data with filters, pagination, and sort |
| `/reviews` | POST | logged-in or nonce | Submit a new review with rating and optional images |
| `/reviews/{id}` | DELETE | `manage_woocommerce` | Remove a review |
| `/reviews/distribution` | GET | public read | Star distribution counts for a product |
| `/settings` | GET, POST | `manage_options` | Retrieve and update plugin settings |

Register routes on `rest_api_init` inside the plugin boot flow or a dedicated module.

## Controller pattern

```php
class ReviewController extends \WP_REST_Controller {

	public function __construct() {
		$this->namespace = 'beplus-advanced-reviews/v1';
		$this->rest_base = 'reviews';
	}

	public function register_routes(): void {
		// register_rest_route()
	}
}
```

## Security

- **`permission_callback`:** Required on every route; never omit it.
- **Public endpoints:** Use read-only permission callbacks only for data that is safe to expose.
- **Settings write:** Require `current_user_can( 'manage_options' )`.
- **Review submission:** Verify nonce, validate capabilities or purchase constraints as required by the feature, and sanitize all user input.
- **Input:** Use `sanitize_text_field()`, `absint()`, `rest_sanitize_boolean()`, and schema validation where possible.
- **Output:** Return normalized arrays or `WP_REST_Response`; escape only at the presentation layer.

## Response shape

Keep review responses predictable:

```php
array(
	'id'         => (int) $review_id,
	'product_id'  => (int) $product_id,
	'rating'      => (int) $rating,
	'author'      => (string) $author_name,
	'content'     => (string) $content,
	'avatar'      => (string) $avatar_url,
	'has_images'  => (bool) $has_images,
	'images'      => array(),
	'created_at'  => (string) $created_at,
)
```

Star distribution response:

```php
array(
	'product_id' => (int) $product_id,
	'total'      => (int) $total_reviews,
	'average'    => (float) $average_rating,
	'stars'      => array(
		'5' => (int) $count_5,
		'4' => (int) $count_4,
		'3' => (int) $count_3,
		'2' => (int) $count_2,
		'1' => (int) $count_1,
	),
)
```

Apply plugin filters before returning results so third-party code can extend queries and response payloads.

```text
❌ register_rest_route without permission_callback
✅ Provide an explicit permission_callback for every method

❌ Echo HTML directly from a REST callback
✅ Return structured JSON or WP_REST_Response data
```

## Reference

| File | Purpose |
|------|---------|
| `src/REST/ReviewController.php` | Public reviews REST (list, submit, filter, distribution) |
| `src/REST/SettingsController.php` | Admin settings REST |
| `src/Core/HookManager.php` | Documented hooks and filters |
