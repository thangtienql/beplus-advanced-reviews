<?php
/**
 * Template: Review Submission Form
 *
 * @package BeplusAdvancedReviewsForWoocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product_id  = $args['product_id'] ?? 0;
$show_images = ! empty( $args['show_images'] );
$paste_enabled = beplus_advanced_reviews_for_woocommerce_is_paste_enabled();
$images_enabled = beplus_advanced_reviews_for_woocommerce_is_images_enabled();
$videos_enabled = beplus_advanced_reviews_for_woocommerce_is_videos_enabled();
$user = wp_get_current_user();
?>
<div class="beplus-advanced-reviews-for-woocommerce__submit-form-wrapper">
	<h3 class="beplus-advanced-reviews-for-woocommerce__submit-form-title"><?php esc_html_e( 'Write a Review', 'beplus-advanced-reviews-for-woocommerce' ); ?></h3>
	<form class="beplus-advanced-reviews-for-woocommerce__submit-form" method="post" enctype="multipart/form-data">
		<input type="hidden" name="product_id" value="<?php echo esc_attr( (string) $product_id ); ?>">

		<div class="beplus-advanced-reviews-for-woocommerce__star-rating">
			<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
				<input
					type="radio"
					id="bpar-star-<?php echo esc_attr( (string) $i ); ?>"
					name="rating"
					value="<?php echo esc_attr( (string) $i ); ?>"
					class="beplus-advanced-reviews-for-woocommerce__star-input"
					required
				>
				<label
					for="bpar-star-<?php echo esc_attr( (string) $i ); ?>"
					class="beplus-advanced-reviews-for-woocommerce__star-label"
					aria-label="<?php
					/* translators: %d: Number of stars */
					printf( esc_attr__( '%d stars', 'beplus-advanced-reviews-for-woocommerce' ), $i );
					?>"
					title="<?php
					/* translators: %d: Number of stars */
					printf( esc_attr__( '%d stars', 'beplus-advanced-reviews-for-woocommerce' ), $i );
					?>"
				>&#9733;</label>
			<?php endfor; ?>
		</div>

		<?php if ( ! $user->exists() ) : ?>
			<input
				type="text"
				name="author"
				placeholder="<?php esc_attr_e( 'Your name *', 'beplus-advanced-reviews-for-woocommerce' ); ?>"
				required
				aria-label="<?php esc_attr_e( 'Your name', 'beplus-advanced-reviews-for-woocommerce' ); ?>"
			>
			<input
				type="email"
				name="email"
				placeholder="<?php esc_attr_e( 'Your email', 'beplus-advanced-reviews-for-woocommerce' ); ?>"
				aria-label="<?php esc_attr_e( 'Your email', 'beplus-advanced-reviews-for-woocommerce' ); ?>"
			>
		<?php endif; ?>

		<textarea
			name="content"
			placeholder="<?php esc_attr_e( 'Write your review...', 'beplus-advanced-reviews-for-woocommerce' ); ?>"
			required
			aria-label="<?php esc_attr_e( 'Review content', 'beplus-advanced-reviews-for-woocommerce' ); ?>"
		></textarea>

		<?php if ( $show_images && $images_enabled ) : ?>
			<div class="beplus-advanced-reviews-for-woocommerce__media-preview" style="display:none;"></div>

			<?php
			$max_image_mb = beplus_advanced_reviews_for_woocommerce_get_settings()['max_image_size_mb'] ?? 2;
			$max_video_mb = beplus_advanced_reviews_for_woocommerce_get_settings()['max_video_size_mb'] ?? 20;
			$accept_types = $videos_enabled
				? 'image/jpeg,image/png,image/webp,video/mp4,video/webm,video/ogg'
				: 'image/jpeg,image/png,image/webp';
			?>

			<div class="beplus-advanced-reviews-for-woocommerce__upload-zone" tabindex="0"
				aria-label="<?php esc_attr_e( 'Upload files — click, drag and drop, or paste from clipboard', 'beplus-advanced-reviews-for-woocommerce' ); ?>">
				<div class="beplus-advanced-reviews-for-woocommerce__upload-zone-content">
					<span class="beplus-advanced-reviews-for-woocommerce__upload-zone-icon" aria-hidden="true">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
							<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
							<polyline points="17 8 12 3 7 8"/>
							<line x1="12" y1="3" x2="12" y2="15"/>
						</svg>
					</span>
					<p class="beplus-advanced-reviews-for-woocommerce__upload-zone-text">
						<?php esc_html_e( 'Click to browse, drag and drop files here, or paste from clipboard', 'beplus-advanced-reviews-for-woocommerce' ); ?>
					</p>
					<p class="beplus-advanced-reviews-for-woocommerce__upload-zone-hint">
						<?php
						/* translators: %s: max image size in MB */
						echo esc_html( sprintf( __( 'Images: JPEG, PNG, WebP (max %s MB)', 'beplus-advanced-reviews-for-woocommerce' ), $max_image_mb ) );
						if ( $videos_enabled ) {
							/* translators: %s: max video size in MB */
							echo ' — ' . esc_html( sprintf( __( 'Videos: MP4, WebM, OGG (max %s MB)', 'beplus-advanced-reviews-for-woocommerce' ), $max_video_mb ) );
						}
						?>
					</p>
				</div>
				<input
					type="file"
					class="beplus-advanced-reviews-for-woocommerce__upload-zone-input"
					name="media[]"
					multiple
					accept="<?php echo esc_attr( $accept_types ); ?>"
					aria-label="<?php esc_attr_e( 'Select files to upload', 'beplus-advanced-reviews-for-woocommerce' ); ?>"
				>
				<?php if ( $paste_enabled ) : ?>
					<input type="hidden" class="beplus-advanced-reviews-for-woocommerce__paste-input" name="paste_image" value="">
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<button type="submit" class="beplus-advanced-reviews-for-woocommerce__submit-btn">
			<?php esc_html_e( 'Submit Review', 'beplus-advanced-reviews-for-woocommerce' ); ?>
		</button>
	</form>
</div>
