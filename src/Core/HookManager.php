<?php
/**
 * HookManager — constants for all plugin hooks and filters.
 *
 * @package BePlusAdvancedReviews
 * @subpackage Core
 */

namespace BePlusAdvancedReviews\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class HookManager {

	public const SERVICES           = 'beplus_advanced_reviews.services';
	public const BLOCKS             = 'beplus_advanced_reviews.blocks';
	public const REVIEW_QUERY       = 'beplus-advanced-reviews/review.query';
	public const REVIEW_RESULTS     = 'beplus-advanced-reviews/review.results';
	public const REVIEW_SUBMITTED   = 'beplus-advanced-reviews/review.submitted';
	public const MEDIA_UPLOADED     = 'beplus-advanced-reviews/media.uploaded';
	public const CUSTOM_POSITION    = 'beplus_advanced_reviews_custom_position';

	public static function template_paths(): string {
		return 'beplus_advanced_reviews_template_paths';
	}
}
