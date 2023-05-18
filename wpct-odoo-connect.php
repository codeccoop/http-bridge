<?php

/**
 * Plugin Name:     Wpct Odoo Connect
 * Plugin URI:      https://git.coopdevs.org/coopdevs/website/wp/wp-plugins
 * Description:     Configure and connect to Odoo API
 * Author:          Coopdevs Treball SCCL
 * Author URI:      https://coopdevs.org
 * Text Domain:     wpct_odoo_connect
 * Domain Path:     /languages
 * Version:         0.1.7
 *
 * @package         Wpct_Odoo_Connect
 */

// JWT Authentication config
define('JWT_AUTH_SECRET_KEY', getenv('WPCT_OC_AUTH_SECRET') ? getenv('WPCT_OC_AUTH_SECRET') : '123456789');
define('JWT_AUTH_CORS_ENABLE', true);

define('WPCT_OC_DEFAULT_LOCALE', getenv('WPCT_OC_DEFAULT_LOCALE') ? getenv('WPCT_OC_DEFAULT_LOCALE') : 'ca');

// Options PAGE
require_once "includes/options-page.php";
require_once 'includes/user-language.php';

// Dependency checker
require_once "includes/dependencies-checker.php";

// API utils
require_once "includes/api-utils.php";

// Define plugin dependencies
$GLOBALS['WPCT_OC_DEPENDENCIES'] = array(
    'JWT Authentication' => 'jwt-authentication-for-wp-rest-api/jwt-auth.php'
);

// Plugin dependencies validation
wpct_oc_check_dependencies();

// Middleware headers setter
function wpct_oc_set_headers($request_headers, $feed, $entry, $form)
{
    $request_headers['API-KEY'] = wpct_oc_get_api_key();
    $request_headers['Accept-Language'] = wpct_oc_accept_language_header();
    return $request_headers;
}
