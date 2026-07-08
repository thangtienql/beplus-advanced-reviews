<?php
/**
 * SchemaManager — create and migrate custom database tables.
 *
 * @package BeplusAdvancedReviewsForWoocommerce
 * @subpackage DB
 */

namespace BeplusAdvancedReviewsForWoocommerce\DB;

use BeplusAdvancedReviewsForWoocommerce\Core\AbstractModule;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SchemaManager extends AbstractModule {

	public function register(): void {
		add_action( 'init', array( $this, 'maybe_create_tables' ) );
	}

	/**
	 * Create tables if schema version is outdated.
	 *
	 * @return void
	 */
	public function maybe_create_tables(): void {
		$current_version = get_option( 'beplus_advanced_reviews_for_woocommerce_for_woocommerce_schema_version', '0' );
		if ( version_compare( $current_version, BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_VERSION, '<' ) ) {
			$this->create_tables();
			update_option( 'beplus_advanced_reviews_for_woocommerce_for_woocommerce_schema_version', BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_VERSION, false );
		}
	}

	/**
	 * Create the custom database tables.
	 *
	 * @return void
	 */
	public function create_tables(): void {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$table_media = $wpdb->prefix . 'bparfw_review_media';

		$sql = "CREATE TABLE {$table_media} (
			id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			comment_id      BIGINT UNSIGNED NOT NULL,
			attachment_id   BIGINT UNSIGNED NOT NULL,
			sort_order      TINYINT UNSIGNED NOT NULL DEFAULT 0,
			created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
			INDEX idx_comment (comment_id)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
