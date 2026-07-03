<?php
/**
 * Common helper functions for BePlus Advanced Reviews.
 *
 * @package BePlusAdvancedReviews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get merged plugin settings (defaults + saved).
 *
 * @return array<string, mixed>
 */
function beplus_advanced_reviews_get_settings(): array {
	static $settings = null;

	if ( null !== $settings ) {
		return $settings;
	}

	$defaults = array(
		'display_mode'     => 'replace',
		'enable_images'    => true,
		'enable_paste'     => true,
		'enable_filter'    => true,
		'enable_sort'      => true,
		'load_more_count'  => 10,
		'rating_threshold' => 0,
		'max_image_size_mb' => 2,
	);

	$saved = get_option( 'beplus_advanced_reviews_settings', array() );
	if ( ! is_array( $saved ) ) {
		$saved = array();
	}

	$settings = array_merge( $defaults, $saved );
	return $settings;
}

/**
 * Get the display mode setting.
 *
 * @return string 'keep' | 'replace'
 */
function beplus_advanced_reviews_get_display_mode(): string {
	$settings = beplus_advanced_reviews_get_settings();
	return isset( $settings['display_mode'] ) ? $settings['display_mode'] : 'replace';
}

/**
 * Get the number of reviews per load.
 *
 * @return int
 */
function beplus_advanced_reviews_get_load_more_count(): int {
	$settings = beplus_advanced_reviews_get_settings();
	return isset( $settings['load_more_count'] ) ? absint( $settings['load_more_count'] ) : 10;
}

/**
 * Check if image attachments are enabled.
 *
 * @return bool
 */
function beplus_advanced_reviews_is_images_enabled(): bool {
	$settings = beplus_advanced_reviews_get_settings();
	return ! empty( $settings['enable_images'] );
}

/**
 * Check if paste support is enabled.
 *
 * @return bool
 */
function beplus_advanced_reviews_is_paste_enabled(): bool {
	$settings = beplus_advanced_reviews_get_settings();
	return ! empty( $settings['enable_paste'] );
}

/**
 * Get max image size in bytes from plugin setting.
 *
 * @return int
 */
function beplus_advanced_reviews_get_max_image_size(): int {
	$settings = beplus_advanced_reviews_get_settings();
	$mb       = isset( $settings['max_image_size_mb'] ) ? absint( $settings['max_image_size_mb'] ) : 2;
	return $mb * 1024 * 1024;
}

/**
 * Render the Advanced Reviews block and return output.
 *
 * @return string|null
 */
function beplus_advanced_reviews_get_block_instance(): ?string {
	if ( ! function_exists( 'do_blocks' ) ) {
		return null;
	}

	$block_content = '<!-- wp:beplus-advanced-reviews/advanced-review /-->';
	return do_blocks( $block_content );
}

/**
 * Get a template from the plugin or theme.
 *
 * @param string $template_name Template name.
 * @param array<string, mixed>  $args          Arguments to pass to the template.
 */
function beplus_advanced_reviews_get_template( string $template_name, array $args = array() ): void {
	$paths = apply_filters(
		'beplus_advanced_reviews_template_paths',
		array(
			get_stylesheet_directory() . '/beplus-advanced-reviews/',
			BEPLUS_ADVANCED_REVIEWS_PLUGIN_DIR . 'templates/',
		)
	);

	foreach ( $paths as $path ) {
		$file = trailingslashit( $path ) . $template_name;
		if ( file_exists( $file ) ) {
			extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
			include $file;
			return;
		}
	}
}

/**
 * Get a product ID from the current context.
 *
 * @return int
 */
function beplus_advanced_reviews_get_current_product_id(): int {
	if ( function_exists( 'wc_get_product' ) ) {
		$product = wc_get_product();
		if ( $product ) {
			return $product->get_id();
		}
	}

	$post_id = get_the_ID();
	if ( $post_id ) {
		return absint( $post_id );
	}

	return 0;
}
