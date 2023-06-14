<?php

/**
 * Plugin Name:     Wpct Odoo Connect
 * Plugin URI:      https://git.coopdevs.org/coopdevs/website/wp/wp-plugins/wpct-odoo-connect
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

// API utils
require_once "includes/api-utils.php";

// Rest API User
register_activation_hook(
    __FILE__,
    'wpct_oc_activate'
);
function wpct_oc_activate()
{
    $user = get_user_by('login', 'wpct_oc_user');
    if ($user) return;
    $user_id = wp_insert_user(array(
        'user_nicename' => 'WPCT OC User',
        'user_login' => 'wpct_oc_user',
        'user_pass' => 'wpct_oc_pass',
        'user_email' => 'wpct_oc_user@wpctoc.com',
        'role' => 'editor',
    ));
    if (is_wp_error($user_id)) {
        throw new Exception($user_id->get_error_message());
    }
}

register_deactivation_hook(__FILE__, 'wpct_oc_deactivate');
function wpct_oc_deactivate()
{
    $user = get_user_by('login', 'wpct_oc_user');
    if ($user) {
        wp_delete_user($user->ID);
    }
}

// Plugin dependencies
add_filter('wpct_dependencies_check', function ($dependencies) {
    $dependencies['JWT Authentication for WP-API'] = '<a href="https://wordpress.org/plugins/jwt-authentication-for-wp-rest-api/">JWT Authentication for WP-API</a>';
    return $dependencies;
});
