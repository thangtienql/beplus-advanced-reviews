<?php
/**
 * Template: Star Distribution (server-side initial render)
 *
 * @package BePlusAdvancedReviews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$product_id = $args['product_id'] ?? 0;

if ( ! $product_id ) {
	return;
}

$repository = new \BePlusAdvancedReviews\Reviews\ReviewRepository();
$data       = $repository->get_star_distribution( $product_id );

if ( ! $data['total'] ) {
	echo '<p class="beplus-advanced-reviews__no-reviews">' . esc_html__( 'No reviews yet.', 'beplus-advanced-reviews' ) . '</p>';
	return;
}
?>
<div class="beplus-advanced-reviews__distribution-header">
	<div class="beplus-advanced-reviews__average">
		<span class="beplus-advanced-reviews__average-value"><?php echo esc_html( number_format_i18n( (float) $data['average'], 1 ) ); ?></span>
		<div>
			<span class="beplus-advanced-reviews__average-stars">
				<?php echo beplus_advanced_reviews_render_stars( (int) round( $data['average'] ), 1 ); // phpcs:ignore ?>
			</span>
			<span class="beplus-advanced-reviews__total">
				<?php
				/* translators: %d: number of reviews */
				printf( esc_html( _n( '%d review', '%d reviews', (int) $data['total'], 'beplus-advanced-reviews' ) ), (int) $data['total'] );
				?>
			</span>
		</div>
	</div>
	<div class="beplus-advanced-reviews__distribution-bars">
		<?php for ( $s = 5; $s >= 1; $s-- ) : ?>
			<?php
			$count   = $data['stars'][ (string) $s ] ?? 0;
			$percent = $data['total'] > 0 ? ( $count / $data['total'] * 100 ) : 0;
			?>
			<div class="beplus-advanced-reviews__distribution-bar-row">
				<span class="beplus-advanced-reviews__distribution-bar-label"><?php echo esc_html( (string) $s ); ?>★</span>
				<div class="beplus-advanced-reviews__distribution-bar-track">
					<div
						class="beplus-advanced-reviews__distribution-bar-fill"
						style="width:<?php echo esc_attr( (string) round( $percent, 2 ) ); ?>%"
						role="progressbar"
						aria-valuenow="<?php echo esc_attr( (string) $count ); ?>"
						aria-valuemin="0"
						aria-valuemax="<?php echo esc_attr( (string) $data['total'] ); ?>"
					></div>
				</div>
				<span class="beplus-advanced-reviews__distribution-bar-count"><?php echo esc_html( (string) $count ); ?></span>
			</div>
		<?php endfor; ?>
	</div>
</div>
