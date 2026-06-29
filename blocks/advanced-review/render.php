<?php
/**
 * Server-side render for the Advanced Reviews block.
 *
 * When block.json specifies "render": "file:./render.php",
 * WordPress includes this file and captures its output.
 * Variables $attributes, $content, and $block are available.
 *
 * @package BePlusAdvancedReviews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product_id = beplus_advanced_reviews_get_current_product_id();

if ( ! $product_id ) {
	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return;
	}
	echo '<p>' . esc_html__( 'Advanced Reviews block requires a product context.', 'beplus-advanced-reviews' ) . '</p>';
	return;
}

$show_distribution = ! empty( $attributes['showDistribution'] );
$show_filter_bar   = ! empty( $attributes['showFilterBar'] );
$show_submit_form  = ! empty( $attributes['showSubmitForm'] );
$show_images       = ! empty( $attributes['showImages'] );
$show_avatar       = ! empty( $attributes['showAvatar'] );
$reviews_per_load  = absint( $attributes['reviewsPerLoad'] ?? 10 );
$enable_lazy_load  = ! empty( $attributes['enableLazyLoad'] );

$wrapper_attrs = get_block_wrapper_attributes(
	array(
		'class'            => 'beplus-advanced-reviews beplus-advanced-reviews--loading',
		'data-product-id'  => $product_id,
		'data-per-page'    => $reviews_per_load,
		'data-show-avatar' => $show_avatar ? '1' : '0',
		'data-show-images' => $show_images ? '1' : '0',
		'data-enable-lazy' => $enable_lazy_load ? '1' : '0',
	)
);

?>
<div <?php echo $wrapper_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>

	<?php if ( $show_distribution ) : ?>
		<div class="beplus-advanced-reviews__distribution" aria-live="polite">
			<?php beplus_advanced_reviews_get_template( 'star-distribution.php', array( 'product_id' => $product_id ) ); ?>
		</div>
	<?php endif; ?>

	<?php if ( $show_filter_bar ) : ?>
		<div class="beplus-advanced-reviews__filter-bar">
			<div class="beplus-advanced-reviews__filter-stars">
				<span class="beplus-advanced-reviews__filter-label"><?php esc_html_e( 'Filter by rating:', 'beplus-advanced-reviews' ); ?></span>
				<?php for ( $i = 5; $i >= 1; $i-- ) : ?>
					<button type="button"
						class="beplus-advanced-reviews__filter-star"
						data-rating="<?php echo esc_attr( $i ); ?>"
						aria-label="<?php printf( esc_attr__( 'Filter by %d stars', 'beplus-advanced-reviews' ), $i ); ?>"
						aria-pressed="false">
						<?php echo esc_html( $i ); ?>★
					</button>
				<?php endfor; ?>
			</div>

			<?php if ( $show_images ) : ?>
				<div class="beplus-advanced-reviews__filter-images">
					<label class="beplus-advanced-reviews__filter-toggle">
						<input type="checkbox" class="beplus-advanced-reviews__filter-images-input" aria-label="<?php esc_attr_e( 'Show only reviews with images', 'beplus-advanced-reviews' ); ?>">
						<?php esc_html_e( 'With images only', 'beplus-advanced-reviews' ); ?>
					</label>
				</div>
			<?php endif; ?>

			<div class="beplus-advanced-reviews__filter-sort">
				<label for="bpar-sort-select" class="beplus-advanced-reviews__filter-label">
					<?php esc_html_e( 'Sort by:', 'beplus-advanced-reviews' ); ?>
				</label>
				<select id="bpar-sort-select" class="beplus-advanced-reviews__sort-select" aria-label="<?php esc_attr_e( 'Sort reviews', 'beplus-advanced-reviews' ); ?>">
					<option value="newest"><?php esc_html_e( 'Newest', 'beplus-advanced-reviews' ); ?></option>
					<option value="oldest"><?php esc_html_e( 'Oldest', 'beplus-advanced-reviews' ); ?></option>
					<option value="highest"><?php esc_html_e( 'Highest rated', 'beplus-advanced-reviews' ); ?></option>
					<option value="lowest"><?php esc_html_e( 'Lowest rated', 'beplus-advanced-reviews' ); ?></option>
				</select>
			</div>
		</div>
	<?php endif; ?>

	<div class="beplus-advanced-reviews__list-container" aria-live="polite">
		<div class="beplus-advanced-reviews__list">
			<?php beplus_advanced_reviews_get_template( 'review-list.php', array( 'product_id' => $product_id, 'show_avatar' => $show_avatar, 'show_images' => $show_images ) ); ?>
		</div>

		<div class="beplus-advanced-reviews__load-more-wrapper">
			<button type="button" class="beplus-advanced-reviews__load-more button" aria-label="<?php esc_attr_e( 'Load more reviews', 'beplus-advanced-reviews' ); ?>">
				<?php esc_html_e( 'Load More', 'beplus-advanced-reviews' ); ?>
				<span class="beplus-advanced-reviews__load-more-spinner" aria-hidden="true"></span>
			</button>
		</div>
	</div>

	<?php if ( $show_submit_form ) : ?>
		<?php beplus_advanced_reviews_get_template( 'review-form.php', array( 'product_id' => $product_id, 'show_images' => $show_images ) ); ?>
	<?php endif; ?>

</div>
