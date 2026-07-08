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
 * Render star rating HTML.
 *
 * @param int   $rating  Rating value (1-5).
 * @param float $size    Optional star size in em.
 * @return string
 */
function beplus_advanced_reviews_for_woocommerce_render_stars( int $rating, float $size = 1.0 ): string {
	$rating = max( 1, min( 5, $rating ) );
	$stars  = '';

	for ( $i = 1; $i <= 5; $i++ ) {
		$filled = $i <= $rating ? ' beplus-advanced-reviews-for-woocommerce__star--filled' : ' beplus-advanced-reviews-for-woocommerce__star--empty';
		$stars .= sprintf(
			'<span class="beplus-advanced-reviews-for-woocommerce__star%s" aria-hidden="true" style="font-size:%fem;">&#9733;</span>',
			esc_attr( $filled ),
			esc_attr( (string) $size )
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
