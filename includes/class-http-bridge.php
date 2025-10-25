<?php

namespace HTTP_BRIDGE;

use WPCT_PLUGIN\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}


/**
 * HTTP Bridge plugin.
 *
 * @package http-bridge
 */
class Http_Bridge extends Plugin {
	/**
	 * Handles plugin's settings store class name.
	 *
	 * @var string $settings_class Plugins settings class name.
	 */
	const STORE = '\HTTP_BRIDGE\Settings_Store';

	/**
	 * Plugin menu class name handle.
	 *
	 * @var string $menu_class Menu class name.
	 */
	const MENU = '\HTTP_BRIDGE\Menu';

	/**
	 * Setup the rest controller and bind wp hooks.
	 *
	 * @param mixed[] ...$args Constructor array of arguments.
	 */
	public function construct( ...$args ) {
		parent::construct( ...$args );

		add_filter(
			'http_bridge_backends',
			static function ( $backends ) {
				if ( ! wp_is_numeric_array( $backends ) ) {
					$backends = array();
				}

				$setting = self::setting( 'general' );
				if ( ! $setting ) {
					return $backends;
				}

				foreach ( $setting->backends ?: array() as $data ) {
					$backends[] = new Backend( $data );
				}

				return $backends;
			},
			10,
			1
		);

		add_filter(
			'http_bridge_credentials',
			static function ( $credentials ) {
				if ( ! wp_is_numeric_array( $credentials ) ) {
					$credentials = array();
				}

				$setting = self::setting( 'general' );
				if ( ! $setting ) {
					return $credentials;
				}

				foreach ( $setting->credentials ?: array() as $data ) {
					$credentials[] = new Credential( $data );
				}

				return $credentials;
			},
			10,
			1
		);
	}
}
