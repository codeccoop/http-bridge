<?php

/**
 * Plugin Name:     HTTP Bridge
 * Plugin URI:      https://git.coopdevs.org/codeccoop/wp/plugins/bridges/http-bridge
 * Description:     Connect WP with backends over HTTP
 * Author:          Codec
 * Author URI:      https://www.codeccoop.org
 * Text Domain:     http-bridge
 * Domain Path:     /languages
 * Version:         1.0.0
 */

namespace HTTP_BRIDGE;

use WPCT_ABSTRACT\Plugin;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('\HTTP_BRIDGE\HTTP_Bridge')) :

    if (!defined('HTTP_BRIDGE_AUTH_SECRET')) {
        define('HTTP_BRIDGE_AUTH_SECRET', getenv('HTTP_BRIDGE_AUTH_SECRET') ? getenv('HTTP_BRIDGE_AUTH_SECRET') : '123456789');
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
     */
    class HTTP_Bridge extends Plugin
    {
        /**
         * Plugin name handle.
         *
         * @var string $name Plugin name.
         */
        public static $name = 'HTTP Bridge';

        /**
         * Plugin textdomain handle.
         *
         * @var string $textdomain Plugin textdomain.
         */
        public static $textdomain = 'http-bridge';

        /**
         * Plugin menu class name handle.
         *
         * @var string $menu_class Menu class name.
         */
        protected static $menu_class = '\HTTP_BRIDGE\Menu';

        /**
         * Setup the rest controller.
         */
        public function __construct()
        {
            parent::__construct();

            add_filter('plugin_action_links', function ($links, $file) {
                if ($file !== plugin_basename(__FILE__)) {
                    return $links;
                }

                $url = admin_url('options-general.php?page=http-bridge');
                $label = __('Settings');
                $link = "<a href='{$url}'>{$label}</a>";
                array_unshift($links, $link);
                return $links;
            }, 5, 2);

            new REST_Controller();
        }

        /**
         * Plugin activation callback.
         */
        public static function activate()
        {
        }

        /**
         * Plugin deactivation callback.
         */
        public static function deactivate()
        {
        }

        /**
         * Plugin initialization.
         */
        public function init()
        {
        }
    }

    register_deactivation_hook(__FILE__, function () {
        HTTP_Bridge::deactivate();
    });

    register_activation_hook(__FILE__, function () {
        HTTP_Bridge::activate();
    });

    add_action('plugins_loaded', function () {
        $plugin = HTTP_Bridge::get_instance();
    }, 10);

endif;
