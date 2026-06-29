<?php
/**
 * Template: Review Card
 *
 * @package BePlusAdvancedReviews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$review     = $args['review'] ?? array();
$show_avatar = ! empty( $args['show_avatar'] );
$show_images = ! empty( $args['show_images'] );
$rating      = (int) ( $review['rating'] ?? 0 );
$author      = esc_html( $review['author'] ?? '' );
$content     = wp_kses_post( $review['content'] ?? '' );
$date_human  = esc_html( $review['date_human'] ?? '' );
$avatar      = esc_url( $review['avatar'] ?? '' );
$images      = $review['images'] ?? array();
?>
<article class="beplus-advanced-reviews__review-card">
	<?php if ( $show_avatar && $avatar ) : ?>
		<div class="beplus-advanced-reviews__review-avatar">
			<img src="<?php echo $avatar; // phpcs:ignore ?>" alt="<?php echo $author; // phpcs:ignore ?>" width="48" height="48" loading="lazy">
		</div>
	<?php endif; ?>
	<div class="beplus-advanced-reviews__review-body">
		<div class="beplus-advanced-reviews__review-header">
			<span class="beplus-advanced-reviews__review-author"><?php echo $author; // phpcs:ignore ?></span>
			<span class="beplus-advanced-reviews__review-rating">
				<?php echo beplus_advanced_reviews_render_stars( $rating ); // phpcs:ignore ?>
			</span>
		</div>
		<div class="beplus-advanced-reviews__review-content"><?php echo $content; // phpcs:ignore ?></div>
		<?php if ( $show_images && $images ) : ?>
			<div class="beplus-advanced-reviews__review-images">
				<?php foreach ( $images as $image ) : ?>
					<a href="<?php echo esc_url( $image['url'] ?? '' ); ?>" class="beplus-advanced-reviews__review-image-link" target="_blank" rel="noopener">
						<img src="<?php echo esc_url( $image['thumbnail'] ?? '' ); ?>" alt="" width="80" height="80" loading="lazy" class="beplus-advanced-reviews__review-image-thumb">
					</a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<div class="beplus-advanced-reviews__review-date"><?php echo $date_human; // phpcs:ignore ?></div>
	</div>
</article>
