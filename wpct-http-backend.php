<?php

namespace WPCT_HB;

/**
 * Plugin Name:     Wpct Http Backend
 * Plugin URI:      https://git.coopdevs.org/coopdevs/website/wp/wp-plugins/wpct-odoo-connect
 * Description:     Configure and connect WP with Bakcend over HTTP requests
 * Author:          Codec Cooperativa
 * Author URI:      https://www.codeccoop.org
 * Text Domain:     wpct-http-backend
 * Domain Path:     /languages
 * Version:         0.1.7
 *
 * @package         Wpct_Http_Backend
 */

if (!defined('ABSPATH')) {
    exit;
}

// JWT Authentication config
define('JWT_AUTH_SECRET_KEY', getenv('WPCT_HB_AUTH_SECRET') ? getenv('WPCT_HB_AUTH_SECRET') : '123456789');
define('JWT_AUTH_CORS_ENABLE', true);

require_once 'includes/class-menu.php';
require_once 'includes/class-settings.php';
require_once "includes/class-api.php";

class Plugin
{
    private $menu;

    public static function activate()
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

    public static function deactivate()
    {
        $user = get_user_by('login', 'wpct_http_user');
        if ($user) {
            wp_delete_user($user->ID);
        }
    }

    public function __construct()
    {
        $settings = new Settings();
        $this->menu = new Menu('Wpct Http Backend', $settings);

        load_plugin_textdomain(
            'wpct-http-backend',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages',
        );
    }

    public function on_load()
    {
        // Plugin dependencies
        add_filter('wpct_dependencies_check', function ($dependencies) {
            $dependencies['JWT Authentication for WP-API'] = '<a href="https://wordpress.org/plugins/jwt-authentication-for-wp-rest-api/">JWT Authentication for WP-API</a>';
            $dependencies['Wpct String Translation'] = '<a href="https://git.coopdevs.org/codeccoop/wp/wpct-string-translation/">Wpct String Translation</a>';
            return $dependencies;
        });

        $this->menu->on_load();
    }
}

register_deactivation_hook(__FILE__, function () {
    Plugin::deactivate();
});

register_activation_hook(__FILE__, function () {
    Plugin::activate();
});

add_action('plugins_loaded', function () {
    $plugin = new Plugin();
    $plugin->on_load();
}, 10);
