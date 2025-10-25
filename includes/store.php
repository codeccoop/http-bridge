<?php

use HTTP_BRIDGE\Backend;
use HTTP_BRIDGE\Credential;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

add_filter( 'wpct_plugin_register_settings', 'http_bridge_register_setting', 9, 2);
add_filter( 'sanitize_option_http-bridge_http', 'http_bridge_sanitize_setting', 9, 1 );

function http_bridge_register_setting( $settings, $group ) {
	if ( 'http-bridge' !== $group ) {
		return $settings;
	}

	$settings[] = array(
		'name'       => 'http',
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
}

function http_bridge_sanitize_setting( $data ) {
	if ( is_wp_error($data) ) {
		return $data;
	}

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
}
