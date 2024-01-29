<?php

/**
 * Plugin Name:     Wpct Http Backend
 * Plugin URI:      https://git.coopdevs.org/coopdevs/website/wp/wp-plugins/wpct-odoo-connect
 * Description:     Configure and connect WP with Bakcend over HTTP requests
 * Author:          Codec Cooperativa
 * Author URI:      https://www.codeccoop.org
 * Text Domain:     wpct_http_backend
 * Domain Path:     /languages
 * Version:         0.1.7
 *
 * @package         Wpct_Http_Backend
 */

// JWT Authentication config
define('JWT_AUTH_SECRET_KEY', getenv('WPCT_HB_AUTH_SECRET') ? getenv('WPCT_HB_AUTH_SECRET') : '123456789');
define('JWT_AUTH_CORS_ENABLE', true);

// Options PAGE
require_once "includes/options-page.php";
require_once 'includes/user-language.php';

// API utils
require_once "includes/api-utils.php";

// Rest API User
register_activation_hook(
    __FILE__,
    'wpct_http_activate'
);

function wpct_http_activate()
{
    $user = get_user_by('login', 'wpct_http_user');
    if ($user) return;

    $site_url = parse_url(get_site_url());
    $user_id = wp_insert_user([
        'user_nicename' => 'Wpct Http User',
        'user_login' => 'wpct_http_user',
        'user_pass' => 'wpct_http_pass',
        'user_email' => 'wpct_http_user@' . $site_url['host'],
        'role' => 'editor',
    ]);

    if (is_wp_error($user_id)) {
        throw new Exception($user_id->get_error_message());
    }
}

register_deactivation_hook(__FILE__, 'wpct_http_deactivate');
function wpct_http_deactivate()
{
    $user = get_user_by('login', 'wpct_http_user');
    if ($user) {
        wp_delete_user($user->ID);
    }
}

// Plugin dependencies
add_filter('wpct_dependencies_check', function ($dependencies) {
    $dependencies['JWT Authentication for WP-API'] = '<a href="https://wordpress.org/plugins/jwt-authentication-for-wp-rest-api/">JWT Authentication for WP-API</a>';
    $dependencies['Wpct String Translation'] = '<a href="https://git.coopdevs.org/codeccoop/wp/wpct-string-translation/">Wpct String Translation</a>';
    return $dependencies;
});
