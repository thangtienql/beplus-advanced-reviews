<?php
/**
 * Container — simple DI container with lazy singleton resolution.
 *
 * @package BePlusAdvancedReviews
 * @subpackage Core
 */

namespace BePlusAdvancedReviews\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Container {

	private array $bindings = array();
	private array $instances = array();

	public function set( string $id, callable $factory ): void {
		$this->bindings[ $id ] = $factory;
	}

	public function get( string $id ) {
		if ( isset( $this->instances[ $id ] ) ) {
			return $this->instances[ $id ];
		}

		if ( isset( $this->bindings[ $id ] ) ) {
			$this->instances[ $id ] = call_user_func( $this->bindings[ $id ], $this );
			return $this->instances[ $id ];
		}

		$instance = new $id( $this );
		$this->instances[ $id ] = $instance;
		return $instance;
	}

	public function register( array $services ): void {
		foreach ( $services as $id => $factory ) {
			if ( is_int( $id ) ) {
				$this->set( $factory, function ( Container $c ) use ( $factory ) {
					return new $factory( $c );
				} );
			} else {
				$this->set( $id, $factory );
			}
		}
	}
}
