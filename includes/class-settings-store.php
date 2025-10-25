<?php

namespace HTTP_BRIDGE;

use WPCT_PLUGIN\Settings_Store as Base_Settings_Store;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Plugin settings store.
 */
class Settings_Store extends Base_Settings_Store {

	/**
	 * Handle plugin settings rest controller class name.
	 *
	 * @var string $rest_controller_class Settings REST Controller class name.
	 */
	protected const rest_controller_class = '\HTTP_BRIDGE\REST_Settings_Controller';

	/**
	 * Registers plugin settings.
	 */
	protected function construct( ...$args ) {
		parent::construct( ...$args );

		add_filter(
			'wpct_plugin_register_settings',
			function ( $settings, $group ) {
				if ( $group !== Http_Bridge::slug() ) {
					return $settings;
				}

				$settings[] = array(
					'name'       => 'general',
					'properties' => array(
						'backends'    => array(
							'type'    => 'array',
							'items'   => Backend::schema(),
							'default' => array(),
						),
						'credentials' => array(
							'type'    => 'array',
							'items'   => Credential::schema(),
							'default' => array(),
						),
					),
					'required'   => array( 'backends', 'credentials' ),
					'default'    => array(
						'backends'    => array(),
						'credentials' => array(),
					),
				);

				return $settings;
			},
			9,
			2
		);

		self::ready(
			static function ( $store ) {
				$store::use_setter(
					'general',
					static function ( $data ) {
						$uniques  = array();
						$backends = array();

						foreach ( $data['backends'] ?? array() as $backend ) {
							if ( ! in_array( $backend['name'], $uniques, true ) ) {
								$uniques[]  = $backend['name'];
								$backends[] = $backend;
							}
						}

						$data['backends'] = $backends;

						$uniques     = array();
						$credentials = array();

						foreach ( $data['credentials'] ?? array() as $credential ) {
							if ( ! in_array( $credential['name'], $uniques, true ) ) {
								$uniques[]     = $credential['name'];
								$credentials[] = $credential;
							}
						}

						$data['credentials'] = $credentials;

						return $data;
					},
					9
				);
			}
		);
	}
}
