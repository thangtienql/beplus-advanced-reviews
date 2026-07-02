<?php
/**
 * Placement — display mode logic (keep/replace/custom hook).
 *
 * @package BePlusAdvancedReviews
 * @subpackage Core
 */

namespace BePlusAdvancedReviews\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Placement extends AbstractModule {

	public function register(): void {
		$mode = beplus_advanced_reviews_get_display_mode();

		switch ( $mode ) {
			case 'replace':
				add_filter( 'woocommerce_product_tabs', array( $this, 'replace_reviews_tab' ), 98 );
				break;
			case 'custom_hook':
				add_action( HookManager::CUSTOM_POSITION, array( $this, 'render_at_custom_hook' ) );
				break;
			case 'keep':
			default:
				break;
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
			'title'    => esc_html__( 'Reviews', 'beplus-advanced-reviews' ),
			'priority' => 30,
			'callback' => array( $this, 'render_block' ),
		);

		return $tabs;
	}

	/**
	 * Render the Advanced Reviews block at a custom hook position.
	 */
	public function render_at_custom_hook(): void {
		$this->render_block();
	}

	/**
	 * Render the block output.
	 */
	public function render_block(): void {
		$block_instance = beplus_advanced_reviews_get_block_instance();
		if ( $block_instance ) {
			echo $block_instance; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}
}
