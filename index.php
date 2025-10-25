<?php

use HTTP_BRIDGE\Backend;
use HTTP_BRIDGE\Credential;
use HTTP_BRIDGE\Settings_Store;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

require_once __DIR__ . '/includes/class-backend.php';
require_once __DIR__ . '/includes/class-credential.php';
require_once __DIR__ . '/includes/class-http-client.php';
require_once __DIR__ . '/includes/class-jwt.php';
require_once __DIR__ . '/includes/class-multipart.php';
require_once __DIR__ . '/includes/jwt.php';
require_once __DIR__ . '/includes/requests.php';
require_once __DIR__ . '/includes/oauth.php';
require_once __DIR__ . '/includes/store.php';

add_filter( 'http_bridge_backends', 'http_bridge_backends', 10, 1 );
add_filter( 'http_bridge_credentials', 'http_bridge_credentials', 10, 1 );

/**
 * Backends public filter callback.
 *
 * @param Backend[] Array of backend instances. .
 *
 * @return Basckend[]
 */
function http_bridge_backends( $backends ) {
	if ( ! wp_is_numeric_array( $backends ) ) {
		$backends = array();
	}

	$setting = Settings_Store::setting( 'http' );

	if ( ! $setting ) {
		return $backends;
	}

	foreach ( $setting->backends ?: array() as $data ) {
		$backends[] = new Backend( $data );
	}

	return $backends;
}

/**
 * Credentials public filter callback.
 *
 * @param Credential[] Array of credential instances.
 *
 * @return Credential[]
 */
function http_bridge_credentials( $credentials ) {
	if ( ! wp_is_numeric_array( $credentials ) ) {
		$credentials = array();
	}

	$setting = Settings_Store::setting( 'http' );

	if ( ! $setting ) {
		return $credentials;
	}

	foreach ( $setting->credentials ?: array() as $data ) {
		$credentials[] = new Credential( $data );
	}

	return $credentials;
}
