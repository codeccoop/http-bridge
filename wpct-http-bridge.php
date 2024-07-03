<?php

/**
 * Plugin Name:     Wpct Http Bridge
 * Plugin URI:      https://git.coopdevs.org/codeccoop/wp/wpct-http-bridge
 * Description:     Connect WP with backends over HTTP requests
 * Author:          Codec
 * Author URI:      https://www.codeccoop.org
 * Text Domain:     wpct-http
 * Domain Path:     /languages
 * Version:         1.0.5
 *
 * @package         Wpct_Http
 */

namespace WPCT_HTTP;

use WPCT_ABSTRACT\Plugin;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('\WPCT_HTTP\Wpct_Http_Bridge')) :

    if (!defined('WPCT_HTTP_AUTH_SECRET')) {
        define('WPCT_HTTP_AUTH_SECRET', getenv('WPCT_HTTP_AUTH_SECRET') ? getenv('WPCT_HTTP_AUTH_SECRET') : '123456789');
    }

    require_once 'abstracts/class-singleton.php';
    require_once 'abstracts/class-plugin.php';
    require_once 'abstracts/class-settings.php';

    require_once 'includes/class-menu.php';
    require_once 'includes/class-settings.php';
    require_once 'includes/class-http-client.php';
    require_once 'includes/class-jwt.php';
    require_once 'includes/class-rest-controller.php';

    class Wpct_Http_Bridge extends Plugin
    {
        protected $name = 'Wpct Http Bridge';
        protected $textdomain = 'wpct-http-bridge';

        public function __construct()
        {
            parent::__construct();

            add_filter('plugin_action_links', function ($links, $file) {
                if ($file !== plugin_basename(__FILE__)) {
                    return $links;
                }

                $url = admin_url('options-general.php?page=wpct-http-bridge');
                $label = __('Settings');
                $link = "<a href='{$url}'>{$label}</a>";
                array_unshift($links, $link);
                return $links;
            }, 5, 2);

            new REST_Controller();
        }

        public static function activate()
        {
            $user = get_user_by('login', 'wpct_http_user');
            if ($user) {
                return;
            }

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

        public function init()
        {
        }
    }

    register_deactivation_hook(__FILE__, function () {
        Wpct_Http_Bridge::deactivate();
    });

    register_activation_hook(__FILE__, function () {
        Wpct_Http_Bridge::activate();
    });

    add_action('plugins_loaded', function () {
        $plugin = Wpct_Http_Bridge::get_instance();
    }, 10);

endif;
