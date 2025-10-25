<?php
/**
 * Plugin Name:     HTTP Bridge
 * Plugin URI:      https://git.coopdevs.org/codeccoop/wp/plugins/bridges/http-bridge
 * Description:     Connect WP with backends over HTTP
 * Author:          codeccoop
 * Author URI:      https://www.codeccoop.org
 * License:         GPLv2 or later
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     http-bridge
 * Domain Path:     /languages
 * Version:         2.0.0
 *
 * @package http-bridge
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

if ( ! class_exists( '\HTTP_BRIDGE\Http_Bridge' ) ) {
	if ( is_file( plugin_dir_path( __FILE__ ) . 'plugin/class-plugin.php' ) ) {
		include_once plugin_dir_path( __FILE__ ) . 'plugin/class-plugin.php';
	}

	require_once __DIR__ . '/includes/class-menu.php';
	require_once __DIR__ . '/includes/class-settings-store.php';
	require_once __DIR__ . '/includes/class-http-client.php';
	require_once __DIR__ . '/includes/class-backend.php';
	require_once __DIR__ . '/includes/class-credential.php';
	require_once __DIR__ . '/includes/class-jwt.php';
	require_once __DIR__ . '/includes/class-rest-settings-controller.php';
	require_once __DIR__ . '/includes/class-http-bridge.php';
	require_once __DIR__ . '/includes/http-requests.php';
}

HTTP_BRIDGE\Http_Bridge::setup();
