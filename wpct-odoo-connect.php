<?php

/**
 * Plugin Name:     Wpct Odoo Connect
 * Plugin URI:      https://git.coopdevs.org/coopdevs/website/wp/wp-plugins
 * Description:     Configure and connect to Odoo API
 * Author:          Coopdevs Treball SCCL
 * Author URI:      https://coopdevs.org
 * Text Domain:     wpct_odoo_connect
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Wpct_Odoo_Connect
 */

// JWT Authentication config
define('JWT_AUTH_SECRET_KEY', getenv('JWT_AUTH_SECRET_KEY', '123456789'));
define('JWT_AUTH_CORS_ENABLE', true);

// Options PAGE
require_once "includes/options-page.php";

// Dependency checker
require_once "includes/dependencies-checker.php";

// Define plugin dependencies
$WPCT_OC_DEPENDENCIES = array(
    'JWT Authentication' => 'jwt-authentication-for-wp-rest-api/jwt-auth.php'
);

// Plugin dependencies validation
wpct_oc_check_dependencies();

// set API KEY on Odoo requests
function wpct_forms_set_headers($request_headers, $feed, $entry, $form)
{
    $ocSettings = get_option("wpct_odoo_connect_settings");
    $request_headers['API-KEY'] = $ocSettings['wpct_odoo_connect_textField_apiKey'];
    return $request_headers;
}
