<?php

/**
 * Plugin Name:     HTTP Bridge
 * Plugin URI:      https://git.coopdevs.org/codeccoop/wp/plugins/bridges/http-bridge
 * Description:     Connect WP with backends over HTTP
 * Author:          Còdec
 * Author URI:      https://www.codeccoop.org
 * Text Domain:     http-bridge
 * Domain Path:     /languages
 * Version:         1.0.1
 */

namespace HTTP_BRIDGE;

use WPCT_ABSTRACT\Plugin;

if (!defined('ABSPATH')) {
    exit();
}

if (!class_exists('\HTTP_BRIDGE\HTTP_Bridge')):
    if (!defined('HTTP_BRIDGE_AUTH_SECRET')) {
        define(
            'HTTP_BRIDGE_AUTH_SECRET',
            getenv('HTTP_BRIDGE_AUTH_SECRET')
                ? getenv('HTTP_BRIDGE_AUTH_SECRET')
                : '123456789'
        );
    }

    /**
     * Handle plugin version.
     *
     * @var string HTTP_BRIDGE_VERSION Current plugin version.
     */
    define('HTTP_BRIDGE_VERSION', '1.0.1');

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
         * Setup the rest controller and bind wp hooks.
         */
        public function __construct()
        {
            parent::__construct();
            REST_Controller::start();

            $this->wp_hooks();
        }

        /**
         * Bind plugin to wp hooks.
         */
        private function wp_hooks()
        {
            add_filter(
                'plugin_action_links',
                function ($links, $file) {
                    if ($file !== plugin_basename(__FILE__)) {
                        return $links;
                    }

                    $url = admin_url('options-general.php?page=http-bridge');
                    $label = __('Settings', 'http-bridge');
                    $link = "<a href='{$url}'>{$label}</a>";
                    array_unshift($links, $link);
                    return $links;
                },
                5,
                2
            );

            // Enqueue plugin admin client scripts
            add_action('admin_enqueue_scripts', function ($admin_page) {
                $this->admin_enqueue_scripts($admin_page);
            });
        }

        /**
         * Enqueue admin client scripts
         *
         * @param string $admin_page Current admin page.
         */
        private function admin_enqueue_scripts($admin_page)
        {
            if ('settings_page_http-bridge' !== $admin_page) {
                return;
            }

            wp_enqueue_script(
                $this->get_textdomain(),
                plugins_url('assets/plugin.bundle.js', __FILE__),
                [
                    'react',
                    'react-jsx-runtime',
                    'wp-api-fetch',
                    'wp-components',
                    'wp-dom-ready',
                    'wp-element',
                    'wp-i18n',
                    'wp-api',
                ],
                HTTP_BRIDGE_VERSION,
                ['in_footer' => true]
            );

            wp_set_script_translations(
                $this->get_textdomain(),
                $this->get_textdomain(),
                plugin_dir_path(__FILE__) . 'languages'
            );

            wp_enqueue_style('wp-components');
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

    add_action(
        'plugins_loaded',
        function () {
            $plugin = HTTP_Bridge::get_instance();
        },
        10
    );
endif;
