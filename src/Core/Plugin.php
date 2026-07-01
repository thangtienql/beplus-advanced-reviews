<?php
/**
 * Plugin — main bootstrap, activate, and deactivate.
 *
 * @package BePlusAdvancedReviews
 * @subpackage Core
 */

namespace BePlusAdvancedReviews\Core;

use BePlusAdvancedReviews\Settings\SettingsRegistry;
use BePlusAdvancedReviews\DB\SchemaManager;
use BePlusAdvancedReviews\Blocks\BlockRegistry;
use BePlusAdvancedReviews\REST\ReviewController;
use BePlusAdvancedReviews\REST\SettingsController;
use BePlusAdvancedReviews\Media\MediaHandler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Plugin {

	private Container $container;

	public function __construct() {
		$this->container = new Container();
	}

	public function boot(): void {
		$this->register_core_services();
		$this->register_services_from_filter();
		$this->boot_registered_modules();

		add_action( 'rest_api_init', array( $this, 'init_rest_controllers' ) );
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_filter( 'block_categories_all', array( $this, 'register_block_category' ) );
	}

	private function register_core_services(): void {
		$this->container->set( SettingsRegistry::class, function ( Container $c ) {
			return new SettingsRegistry( $c );
		} );

		$this->container->set( SchemaManager::class, function ( Container $c ) {
			return new SchemaManager( $c );
		} );

		$this->container->set( BlockRegistry::class, function ( Container $c ) {
			return new BlockRegistry( $c );
		} );

		$this->container->set( AssetLoader::class, function ( Container $c ) {
			return new AssetLoader( $c );
		} );

		$this->container->set( Placement::class, function ( Container $c ) {
			return new Placement( $c );
		} );

		$this->container->set( MediaHandler::class, function ( Container $c ) {
			return new MediaHandler( $c );
		} );
	}

	private function register_services_from_filter(): void {
		$services = apply_filters( HookManager::SERVICES, array() );
		$this->container->register( $services );
	}

	private function boot_registered_modules(): void {
		$modules = array(
			SettingsRegistry::class,
			SchemaManager::class,
			BlockRegistry::class,
			AssetLoader::class,
			Placement::class,
		);

		foreach ( $modules as $module_class ) {
			$module = $this->container->get( $module_class );
			$module->register();
		}
	}

	public function init_rest_controllers(): void {
		$review_controller = new ReviewController();
		$review_controller->register_routes();

		$settings_controller = new SettingsController();
		$settings_controller->register_routes();
	}

	public function register_block_category( array $categories ): array {
		$categories[] = array(
			'slug'  => 'beplus-advanced-reviews',
			'title' => __( 'BePlus Advanced Reviews', 'beplus-advanced-reviews' ),
		);

		return $categories;
	}

	public function load_textdomain(): void {
		load_plugin_textdomain(
			'beplus-advanced-reviews',
			false,
			dirname( BEPLUS_ADVANCED_REVIEWS_PLUGIN_BASENAME ) . '/languages'
		);
	}

	public function activate(): void {
		$schema = new SchemaManager( $this->container );
		$schema->create_tables();
		flush_rewrite_rules();
	}

	public function deactivate(): void {
		flush_rewrite_rules();
	}
}
