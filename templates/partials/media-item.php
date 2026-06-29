<?php
/**
 * Template partial: Media Item
 *
 * @package BePlusAdvancedReviews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$url       = esc_url( $args['url'] ?? '' );
$thumbnail = esc_url( $args['thumbnail'] ?? '' );
$alt       = esc_attr( $args['alt'] ?? '' );
?>
<a href="<?php echo $url; // phpcs:ignore ?>" class="beplus-advanced-reviews__review-image-link" target="_blank" rel="noopener">
	<img src="<?php echo $thumbnail; // phpcs:ignore ?>" alt="<?php echo $alt; // phpcs:ignore ?>" width="80" height="80" loading="lazy" class="beplus-advanced-reviews__review-image-thumb">
</a>
