<?php
/**
 * Namespaced helper functions for BePlus Advanced Reviews.
 *
 * @package BePlusAdvancedReviews
 */

namespace BePlusAdvancedReviews\Functions;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get plugin settings via the namespace.
 *
 * @return array<string, mixed>
 */
function get_settings(): array {
	return function_exists( 'beplus_advanced_reviews_get_settings' )
		? beplus_advanced_reviews_get_settings()
		: array();
}

/**
 * Get the display mode via the namespace.
 *
 * @return string
 */
function get_display_mode(): string {
	return function_exists( 'beplus_advanced_reviews_get_display_mode' )
		? beplus_advanced_reviews_get_display_mode()
		: 'replace';
}
