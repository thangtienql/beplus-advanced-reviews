<?php
/**
 * Install and uninstall helpers for Beplus Advanced Reviews For Woocommerce.
 *
 * @package BeplusAdvancedReviewsForWoocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Set default options on activation.
 *
 * @return void
 */
function beplus_advanced_reviews_for_woocommerce_install_defaults(): void {
	$existing = get_option( 'beplus_advanced_reviews_for_woocommerce_for_woocommerce_settings', null );

	if ( null === $existing ) {
		$defaults = array(
			'display_mode'    => 'replace',
			'enable_images'   => true,
			'enable_paste'    => true,
			'enable_filter'   => true,
			'enable_sort'     => true,
			'load_more_count' => 10,
			'rating_threshold' => 0,
			'max_image_size_mb' => 2,
			'enable_videos'    => false,
			'max_video_size_mb' => 20,
		);
		update_option( 'beplus_advanced_reviews_for_woocommerce_for_woocommerce_settings', $defaults, false );
	}

	update_option( 'beplus_advanced_reviews_for_woocommerce_for_woocommerce_schema_version', BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_VERSION, false );
}
