<?php
/**
 * Plugin Name: Beplus Advanced Reviews For Woocommerce
 * Plugin URI:  https://github.com/thangtienql/beplus-advanced-reviews-for-woocommerce
 * Description: Modern WooCommerce product reviews with image support, star distribution, AJAX filtering, and load more.
 * Version:     1.0.0
 * Author:      Beplus
 * Author URI:  https://beplusthemes.com/
 * Text Domain: beplus-advanced-reviews-for-woocommerce
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package BeplusAdvancedReviewsForWoocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_VERSION', '1.0.0' );
define( 'BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

$autoload = BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_PLUGIN_DIR . 'vendor/autoload.php';
if ( file_exists( $autoload ) ) {
	require_once $autoload;
} else {
	spl_autoload_register(
		function ( string $class_name ) {
			$prefix = 'BeplusAdvancedReviewsForWoocommerce\\';
			if ( strncmp( $class_name, $prefix, strlen( $prefix ) ) !== 0 ) {
				return;
			}

			$file = BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_PLUGIN_DIR
				. 'src/'
				. str_replace( '\\', '/', substr( $class_name, strlen( $prefix ) ) )
				. '.php';

			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
	);
}

require_once BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_PLUGIN_DIR . 'includes/common.php';
require_once BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_PLUGIN_DIR . 'includes/hooks.php';

/**
 * Boot plugin.
 *
 * @return \BeplusAdvancedReviewsForWoocommerce\Core\Plugin
 */
function beplus_advanced_reviews_for_woocommerce_boot() {
	static $plugin = null;

	if ( null === $plugin ) {
		$plugin = new \BeplusAdvancedReviewsForWoocommerce\Core\Plugin();
		$plugin->boot();
	}

	return $plugin;
}

add_action( 'plugins_loaded', 'beplus_advanced_reviews_for_woocommerce_init' );

/**
 * Init on plugins_loaded.
 *
 * @return void
 */
function beplus_advanced_reviews_for_woocommerce_init() {
	beplus_advanced_reviews_for_woocommerce_boot();
}

register_activation_hook( __FILE__, 'beplus_advanced_reviews_for_woocommerce_activate' );
register_deactivation_hook( __FILE__, 'beplus_advanced_reviews_for_woocommerce_deactivate' );

/**
 * Activation handler.
 *
 * @return void
 */
function beplus_advanced_reviews_for_woocommerce_activate() {
	if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			esc_html__( 'Beplus Advanced Reviews For Woocommerce requires PHP 7.4 or higher.', 'beplus-advanced-reviews-for-woocommerce' ),
			'Plugin Activation Error',
			array( 'back_link' => true )
		);
	}

	( new \BeplusAdvancedReviewsForWoocommerce\Core\Plugin() )->activate();
}

/**
 * Deactivation handler.
 *
 * @return void
 */
function beplus_advanced_reviews_for_woocommerce_deactivate() {
	( new \BeplusAdvancedReviewsForWoocommerce\Core\Plugin() )->deactivate();
}
