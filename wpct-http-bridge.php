<?php

/**
 * Plugin Name:     Wpct Http Bridge
 * Plugin URI:      https://git.coopdevs.org/codeccoop/wp/plugins/wpct-http-bridge
 * Description:     Connect WP with backends over HTTP requests
 * Author:          Codec
 * Author URI:      https://www.codeccoop.org
 * Text Domain:     wpct-http
 * Domain Path:     /languages
 * Version:         3.0.2
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
    require_once 'abstracts/class-menu.php';
    require_once 'abstracts/class-settings.php';

    require_once 'includes/class-menu.php';
    require_once 'includes/class-settings.php';
    require_once 'includes/class-http-client.php';
    require_once 'includes/class-http-backend.php';
    require_once 'includes/class-jwt.php';
    require_once 'includes/class-rest-controller.php';

    /**
     * HTTP Bridge plugin.
     *
     * @since 1.0.0
     */
    class Wpct_Http_Bridge extends Plugin
    {
        /**
         * Plugin name handle.
         *
         * @var string $name Plugin name.
         *
         * @since 1.0.0
         */
        public static $name = 'Wpct Http Bridge';

        /**
         * Plugin textdomain handle.
         *
         * @var string $textdomain Plugin textdomain.
         *
         * @since 1.0.0
         */
        public static $textdomain = 'wpct-http-bridge';

        /**
         * Plugin menu class name handle.
         *
         * @var string $menu_class Menu class name.
         *
         * @since 1.0.0
         */
        protected static $menu_class = '\WPCT_HTTP\Menu';

        /**
         * Setup the rest controller.
         *
         * @since 1.0.0
         */
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

        /**
         * Plugin activation callback.
         *
         * @since 1.0.0
         */
        public static function activate()
        {
        }

        /**
         * Plugin deactivation callback.
         *
         * @since 1.0.0
         */
        public static function deactivate()
        {
        }

        /**
         * Plugin initialization.
         *
         * @since 1.0.0
         */
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
