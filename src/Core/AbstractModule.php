<?php
/**
 * AbstractModule — base class for all plugin modules.
 *
 * @package BeplusAdvancedReviewsForWoocommerce
 * @subpackage Core
 */

namespace BeplusAdvancedReviewsForWoocommerce\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class AbstractModule {

	protected Container $container;
	protected string $version;
	protected string $plugin_dir;
	protected string $plugin_url;

	public function __construct( Container $container ) {
		$this->container  = $container;
		$this->version    = BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_VERSION;
		$this->plugin_dir = BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_PLUGIN_DIR;
		$this->plugin_url = BEPLUS_ADVANCED_REVIEWS_FOR_WOOCOMMERCE_PLUGIN_URL;
	}

	/**
	 * Register WordPress hooks. Called ONCE during boot.
	 */
	abstract public function register(): void;
}
