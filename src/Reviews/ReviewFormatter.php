<?php
/**
 * ReviewFormatter — shapes review data for API responses.
 *
 * @package BePlusAdvancedReviews
 * @subpackage Reviews
 */

namespace BePlusAdvancedReviews\Reviews;

use BePlusAdvancedReviews\Media\MediaHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ReviewFormatter {

	private MediaHandler $media_handler;

	public function __construct( MediaHandler $media_handler ) {
		$this->media_handler = $media_handler;
	}

	/**
	 * Format a single review row into the API shape.
	 *
	 * @param object $review Database row.
	 * @return array
	 */
	public function format( object $review ): array {
		$comment_id = (int) $review->comment_ID;
		$rating     = isset( $review->rating_value ) ? (int) $review->rating_value : 0;
		$images     = $this->media_handler->get_review_media( $comment_id );

		return array(
			'id'         => $comment_id,
			'product_id'  => (int) $review->comment_post_ID,
			'rating'      => $rating,
			'author'      => $review->comment_author ?? '',
			'content'     => wp_kses_post( $review->comment_content ?? '' ),
			'avatar'      => get_avatar_url( $review->comment_author_email ?? '', array( 'size' => 64 ) ),
			'has_images'  => ! empty( $images ),
			'images'      => $images,
			'created_at'  => $review->comment_date ?? '',
			'date_human'  => sprintf(
				/* translators: %s: human-readable time difference */
				__( '%s ago', 'beplus-advanced-reviews' ),
				human_time_diff( strtotime( $review->comment_date ?? '' ) )
			),
		);
	}

	/**
	 * Format a list of reviews.
	 *
	 * @param array $reviews Array of database rows.
	 * @return array
	 */
	public function format_list( array $reviews ): array {
		$formatted = array();

		foreach ( $reviews as $review ) {
			$formatted[] = $this->format( $review );
		}

		return $formatted;
	}
}
