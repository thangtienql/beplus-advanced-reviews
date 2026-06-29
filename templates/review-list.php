<?php
/**
 * Template: Review List (initial server-side placeholder)
 *
 * @package BePlusAdvancedReviews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product_id  = $args['product_id'] ?? 0;
$show_avatar = ! empty( $args['show_avatar'] );
$show_images = ! empty( $args['show_images'] );

if ( ! $product_id ) {
	return;
}

$repository = new \BePlusAdvancedReviews\Reviews\ReviewRepository();
$result     = $repository->get_reviews( $product_id, array( 'per_page' => beplus_advanced_reviews_get_load_more_count() ) );
$reviews    = $result['reviews'] ?? array();

if ( empty( $reviews ) ) {
	echo '<p class="beplus-advanced-reviews__no-reviews">' . esc_html__( 'No reviews yet.', 'beplus-advanced-reviews' ) . '</p>';
	return;
}

$media_handler = new \BePlusAdvancedReviews\Media\MediaHandler( new \BePlusAdvancedReviews\Core\Container() );
$formatter     = new \BePlusAdvancedReviews\Reviews\ReviewFormatter( $media_handler );
$formatted     = $formatter->format_list( $reviews );

foreach ( $formatted as $review ) {
	beplus_advanced_reviews_get_template( 'review-card.php', array(
		'review'      => $review,
		'show_avatar' => $show_avatar,
		'show_images' => $show_images,
	) );
}
