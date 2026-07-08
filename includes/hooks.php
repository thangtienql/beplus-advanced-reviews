<?php
/**
 * Hook registrations for Beplus Advanced Reviews For Woocommerce.
 *
 * @package BeplusAdvancedReviewsForWoocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'beplus_advanced_reviews_for_woocommerce_register_block_template' );

/**
 * Register block template for Single Product pages.
 *
 * @return void
 */
function beplus_advanced_reviews_for_woocommerce_register_block_template(): void {
	if ( ! function_exists( 'register_block_template' ) ) {
		return;
	}

	$post_type = get_post_type_object( 'product' );
	if ( ! $post_type ) {
		return;
	}

	$template_content = array(
		array(
			'beplus-advanced-reviews/advanced-review',
			array(
				'showDistribution' => true,
				'showFilterBar'    => true,
				'showSubmitForm'   => true,
				'showImages'       => true,
				'showAvatar'       => true,
				'reviewsPerLoad'   => beplus_advanced_reviews_for_woocommerce_get_load_more_count(),
				'enableLazyLoad'   => true,
			),
		),
	);

	$post_type->template = $template_content;
}

/**
 * Return SVG star icon markup.
 *
 * @return string
 */
function beplus_advanced_reviews_for_woocommerce_star_icon(): string {
	return '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>';
}

/**
 * Render star rating HTML.
 *
 * @param int   $rating  Rating value (1-5).
 * @param float $size    Optional star size in em.
 * @return string
 */
function beplus_advanced_reviews_for_woocommerce_render_stars( int $rating, float $size = 1.0 ): string {
	$rating    = max( 1, min( 5, $rating ) );
	$size_px   = 16 * $size;
	$stars     = '';

	for ( $i = 1; $i <= 5; $i++ ) {
		$filled = $i <= $rating ? ' beplus-advanced-reviews-for-woocommerce__star--filled' : ' beplus-advanced-reviews-for-woocommerce__star--empty';
		$stars .= sprintf(
			'<span class="beplus-advanced-reviews-for-woocommerce__star%s" aria-hidden="true" style="width:%fem;height:%fem;"><svg width="%f" height="%f" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></span>',
			esc_attr( $filled ),
			esc_attr( (string) $size ),
			esc_attr( (string) $size ),
			(float) $size_px,
			(float) $size_px
		);
	}

	return sprintf(
		'<span class="beplus-advanced-reviews-for-woocommerce__stars" aria-label="%s">%s</span>',
		sprintf(
			/* translators: %d: rating value */
			esc_attr__( '%d out of 5 stars', 'beplus-advanced-reviews-for-woocommerce' ),
			$rating
		),
		$stars
	);
}
