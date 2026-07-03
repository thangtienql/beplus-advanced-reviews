<?php
/**
 * Template: Review Submission Form
 *
 * @package BePlusAdvancedReviews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product_id  = $args['product_id'] ?? 0;
$show_images = ! empty( $args['show_images'] );
$paste_enabled = beplus_advanced_reviews_is_paste_enabled();
$images_enabled = beplus_advanced_reviews_is_images_enabled();
$videos_enabled = beplus_advanced_reviews_is_videos_enabled();
$user = wp_get_current_user();
?>
<div class="beplus-advanced-reviews__submit-form-wrapper">
	<h3 class="beplus-advanced-reviews__submit-form-title"><?php esc_html_e( 'Write a Review', 'beplus-advanced-reviews' ); ?></h3>
	<form class="beplus-advanced-reviews__submit-form" method="post" enctype="multipart/form-data">
		<input type="hidden" name="product_id" value="<?php echo esc_attr( (string) $product_id ); ?>">

		<div class="beplus-advanced-reviews__star-rating">
			<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
				<input
					type="radio"
					id="bpar-star-<?php echo esc_attr( (string) $i ); ?>"
					name="rating"
					value="<?php echo esc_attr( (string) $i ); ?>"
					class="beplus-advanced-reviews__star-input"
					required
				>
				<label
					for="bpar-star-<?php echo esc_attr( (string) $i ); ?>"
					class="beplus-advanced-reviews__star-label"
					aria-label="<?php
					/* translators: %d: Number of stars */
					printf( esc_attr__( '%d stars', 'beplus-advanced-reviews' ), $i );
					?>"
					title="<?php
					/* translators: %d: Number of stars */
					printf( esc_attr__( '%d stars', 'beplus-advanced-reviews' ), $i );
					?>"
				>&#9733;</label>
			<?php endfor; ?>
		</div>

		<?php if ( ! $user->exists() ) : ?>
			<input
				type="text"
				name="author"
				placeholder="<?php esc_attr_e( 'Your name *', 'beplus-advanced-reviews' ); ?>"
				required
				aria-label="<?php esc_attr_e( 'Your name', 'beplus-advanced-reviews' ); ?>"
			>
			<input
				type="email"
				name="email"
				placeholder="<?php esc_attr_e( 'Your email', 'beplus-advanced-reviews' ); ?>"
				aria-label="<?php esc_attr_e( 'Your email', 'beplus-advanced-reviews' ); ?>"
			>
		<?php endif; ?>

		<textarea
			name="content"
			placeholder="<?php esc_attr_e( 'Write your review...', 'beplus-advanced-reviews' ); ?>"
			required
			aria-label="<?php esc_attr_e( 'Review content', 'beplus-advanced-reviews' ); ?>"
		></textarea>

		<?php if ( $show_images && $images_enabled ) : ?>
			<div class="beplus-advanced-reviews__file-upload">
				<label for="bpar-file-input" class="beplus-advanced-reviews__file-label">
					<?php esc_html_e( 'Add images', 'beplus-advanced-reviews' ); ?>
				</label>
				<input
					type="file"
					id="bpar-file-input"
					name="media[]"
					multiple
					accept="image/jpeg,image/png,image/webp"
					aria-label="<?php esc_attr_e( 'Upload review images', 'beplus-advanced-reviews' ); ?>"
				>
				<p class="beplus-advanced-reviews__file-hint">
					<?php
					printf(
						/* translators: %s: max image size in MB */
						esc_html__( 'Accepted formats: JPEG, PNG, WebP (max %s MB per image)', 'beplus-advanced-reviews' ),
						esc_html( (string) ( beplus_advanced_reviews_get_settings()['max_image_size_mb'] ?? 2 ) )
					);
					?>
				</p>
			</div>

			<?php if ( $videos_enabled ) : ?>
				<div class="beplus-advanced-reviews__file-upload">
					<label for="bpar-video-input" class="beplus-advanced-reviews__file-label">
						<?php esc_html_e( 'Add videos', 'beplus-advanced-reviews' ); ?>
					</label>
					<input
						type="file"
						id="bpar-video-input"
						name="media[]"
						multiple
						accept="video/mp4,video/webm,video/ogg"
						aria-label="<?php esc_attr_e( 'Upload review videos', 'beplus-advanced-reviews' ); ?>"
					>
					<p class="beplus-advanced-reviews__file-hint">
						<?php
						printf(
							/* translators: %s: max video size in MB */
							esc_html__( 'Accepted formats: MP4, WebM, OGG (max %s MB per video)', 'beplus-advanced-reviews' ),
							esc_html( (string) ( beplus_advanced_reviews_get_settings()['max_video_size_mb'] ?? 20 ) )
						);
						?>
					</p>
				</div>
			<?php endif; ?>

			<?php if ( $paste_enabled ) : ?>
				<div class="beplus-advanced-reviews__paste-area" tabindex="0" aria-label="<?php esc_attr_e( 'Paste image from clipboard', 'beplus-advanced-reviews' ); ?>">
					<p><?php esc_html_e( 'Or paste an image from clipboard here', 'beplus-advanced-reviews' ); ?></p>
					<input type="hidden" class="beplus-advanced-reviews__paste-input" name="paste_image" value="">
				</div>
			<?php endif; ?>
		<?php endif; ?>

		<button type="submit" class="beplus-advanced-reviews__submit-btn">
			<?php esc_html_e( 'Submit Review', 'beplus-advanced-reviews' ); ?>
		</button>
	</form>
</div>
