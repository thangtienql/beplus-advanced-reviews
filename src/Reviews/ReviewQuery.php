<?php
/**
 * ReviewQuery — builds and modifies review queries.
 *
 * @package BePlusAdvancedReviews
 * @subpackage Reviews
 */

namespace BePlusAdvancedReviews\Reviews;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class ReviewQuery {

	/**
	 * Build query args for the repository.
	 *
	 * @param array $params Raw request parameters.
	 * @return array
	 */
	public function build_args( array $params ): array {
		$args = array();

		if ( isset( $params['page'] ) ) {
			$args['page'] = absint( $params['page'] );
		}

		if ( isset( $params['per_page'] ) ) {
			$args['per_page'] = absint( $params['per_page'] );
		}

		if ( isset( $params['rating'] ) ) {
			$args['rating'] = absint( $params['rating'] );
		}

		if ( isset( $params['has_images'] ) ) {
			$args['has_images'] = rest_sanitize_boolean( $params['has_images'] );
		}

		if ( isset( $params['sort'] ) ) {
			$allowed_sorts = array( 'newest', 'oldest', 'highest', 'lowest' );
			$sort          = sanitize_text_field( $params['sort'] );
			if ( in_array( $sort, $allowed_sorts, true ) ) {
				$args['sort'] = $sort;
			}
		}

		$args = apply_filters( 'beplus-advanced-reviews/review.query', $args, $params );

		return $args;
	}
}
