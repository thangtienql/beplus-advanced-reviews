<?php
/**
 * AbstractModule — base class for all plugin modules.
 *
 * @package BePlusAdvancedReviews
 * @subpackage Core
 */

namespace BePlusAdvancedReviews\Core;

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
		$this->version    = BEPLUS_ADVANCED_REVIEWS_VERSION;
		$this->plugin_dir = BEPLUS_ADVANCED_REVIEWS_PLUGIN_DIR;
		$this->plugin_url = BEPLUS_ADVANCED_REVIEWS_PLUGIN_URL;
	}

	/**
	 * Register WordPress hooks. Called ONCE during boot.
	 */
	abstract public function register(): void;
}
