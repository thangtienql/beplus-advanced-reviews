<?php
/**
 * Install and uninstall helpers for BePlus Advanced Reviews.
 *
 * @package BePlusAdvancedReviews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Set default options on activation.
 *
 * @return void
 */
function beplus_advanced_reviews_install_defaults(): void {
	$existing = get_option( 'beplus_advanced_reviews_settings', null );

	if ( null === $existing ) {
		$defaults = array(
			'display_mode'    => 'replace',
			'enable_images'   => true,
			'enable_paste'    => true,
			'enable_filter'   => true,
			'enable_sort'     => true,
			'load_more_count' => 10,
			'rating_threshold' => 0,
		);
		update_option( 'beplus_advanced_reviews_settings', $defaults, false );
	}

	update_option( 'beplus_advanced_reviews_schema_version', BEPLUS_ADVANCED_REVIEWS_VERSION, false );
}
