<?php
/**
 * Placement — display mode logic (keep/replace).
 *
 * @package BeplusAdvancedReviewsForWoocommerce
 * @subpackage Core
 */

namespace BeplusAdvancedReviewsForWoocommerce\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Placement extends AbstractModule {

	public function register(): void {
		$mode = beplus_advanced_reviews_for_woocommerce_get_display_mode();

		if ( 'replace' === $mode ) {
			add_filter( 'woocommerce_product_tabs', array( $this, 'replace_reviews_tab' ), 98 );
		}
	}

	/**
	 * Replace the default WooCommerce reviews tab with Advanced Reviews.
	 *
	 * @param array<string, mixed> $tabs
	 * @return array<string, mixed>
	 */
	public function replace_reviews_tab( array $tabs ): array {
		unset( $tabs['reviews'] );

		$tabs['advanced_reviews'] = array(
			'title'    => esc_html__( 'Reviews', 'beplus-advanced-reviews-for-woocommerce' ),
			'priority' => 30,
			'callback' => array( $this, 'render_block' ),
		);

		return $tabs;
	}

	/**
	 * Render the block output.
	 */
	public function render_block(): void {
		$block_instance = beplus_advanced_reviews_for_woocommerce_get_block_instance();
		if ( $block_instance ) {
			echo $block_instance; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}
