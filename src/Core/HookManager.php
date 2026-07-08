<?php
/**
 * HookManager — constants for all plugin hooks and filters.
 *
 * @package BeplusAdvancedReviewsForWoocommerce
 * @subpackage Core
 */

namespace BeplusAdvancedReviewsForWoocommerce\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HookManager {

	public const SERVICES           = 'beplus_advanced_reviews.services';
	public const BLOCKS             = 'beplus_advanced_reviews.blocks';
	public const REVIEW_QUERY       = 'beplus-advanced-reviews-for-woocommerce/review.query';
	public const REVIEW_RESULTS     = 'beplus-advanced-reviews-for-woocommerce/review.results';
	public const REVIEW_SUBMITTED   = 'beplus-advanced-reviews-for-woocommerce/review.submitted';
	public const MEDIA_UPLOADED     = 'beplus-advanced-reviews-for-woocommerce/media.uploaded';
	public const MEDIA_DELETED      = 'beplus-advanced-reviews-for-woocommerce/media.deleted';

	public static function template_paths(): string {
		return 'beplus_advanced_reviews_for_woocommerce_template_paths';
	}
}
