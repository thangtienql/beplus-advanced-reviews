<?php
/**
 * Plugin Name: BePlus Advanced Reviews
 * Plugin URI:  https://beplusthemes.com/
 * Description: Modern WooCommerce product reviews with image support, star distribution, AJAX filtering, and load more.
 * Version:     1.0.0
 * Author:      Beplus
 * Author URI:  https://beplusthemes.com/
 * Text Domain: beplus-advanced-reviews
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * License:     GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package BePlusAdvancedReviews
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'BEPLUS_ADVANCED_REVIEWS_VERSION', '1.0.0' );
define( 'BEPLUS_ADVANCED_REVIEWS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BEPLUS_ADVANCED_REVIEWS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BEPLUS_ADVANCED_REVIEWS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

$autoload = BEPLUS_ADVANCED_REVIEWS_PLUGIN_DIR . 'vendor/autoload.php';
if ( file_exists( $autoload ) ) {
	require_once $autoload;
} else {
	spl_autoload_register(
		function ( string $class_name ) {
			$prefix = 'BePlusAdvancedReviews\\';
			if ( strncmp( $class_name, $prefix, strlen( $prefix ) ) !== 0 ) {
				return;
			}

			$file = BEPLUS_ADVANCED_REVIEWS_PLUGIN_DIR
				. 'src/'
				. str_replace( '\\', '/', substr( $class_name, strlen( $prefix ) ) )
				. '.php';

			if ( file_exists( $file ) ) {
				require_once $file;
			}
		}
	);
}

require_once BEPLUS_ADVANCED_REVIEWS_PLUGIN_DIR . 'includes/common.php';
require_once BEPLUS_ADVANCED_REVIEWS_PLUGIN_DIR . 'includes/hooks.php';

/**
 * Boot plugin.
 *
 * @return \BePlusAdvancedReviews\Core\Plugin
 */
function beplus_advanced_reviews_boot() {
	static $plugin = null;

	if ( null === $plugin ) {
		$plugin = new \BePlusAdvancedReviews\Core\Plugin();
		$plugin->boot();
	}

	return $plugin;
}

add_action( 'plugins_loaded', 'beplus_advanced_reviews_init' );

/**
 * Init on plugins_loaded.
 *
 * @return void
 */
function beplus_advanced_reviews_init() {
	beplus_advanced_reviews_boot();
}

register_activation_hook( __FILE__, 'beplus_advanced_reviews_activate' );
register_deactivation_hook( __FILE__, 'beplus_advanced_reviews_deactivate' );

/**
 * Activation handler.
 *
 * @return void
 */
function beplus_advanced_reviews_activate() {
	if ( version_compare( PHP_VERSION, '7.4', '<' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die(
			esc_html__( 'BePlus Advanced Reviews requires PHP 7.4 or higher.', 'beplus-advanced-reviews' ),
			'Plugin Activation Error',
			array( 'back_link' => true )
		);
	}

	( new \BePlusAdvancedReviews\Core\Plugin() )->activate();
}

/**
 * Deactivation handler.
 *
 * @return void
 */
function beplus_advanced_reviews_deactivate() {
	( new \BePlusAdvancedReviews\Core\Plugin() )->deactivate();
}
