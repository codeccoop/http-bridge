<?php

/**
 * Plugin Name:     HTTP Bridge
 * Plugin URI:      https://git.coopdevs.org/codeccoop/wp/plugins/bridges/http-bridge
 * Description:     Connect WP with backends over HTTP
 * Author:          Còdec
 * Author URI:      https://www.codeccoop.org
 * Text Domain:     http-bridge
 * Domain Path:     /languages
 * Version:         1.2.0
 */

namespace HTTP_BRIDGE;

use WPCT_ABSTRACT\Plugin;

if (!defined('ABSPATH')) {
    exit();
}

if (!class_exists('\HTTP_BRIDGE\HTTP_Bridge')) {
    /**
     * Handle plugin version.
     *
     * @var string HTTP_BRIDGE_VERSION Current plugin version.
     */
    define('HTTP_BRIDGE_VERSION', '1.2.0');

    if (!defined('HTTP_BRIDGE_AUTH_SECRET')) {
        /**
         * Handle plugin encryption secret.
         *
         * @var string HTTP_BRIDGE_AUTH_SECRET Token encryption secret.
         */
        define(
            'HTTP_BRIDGE_AUTH_SECRET',
            getenv('HTTP_BRIDGE_AUTH_SECRET')
                ? getenv('HTTP_BRIDGE_AUTH_SECRET')
                : '@#%5&mjx44yQRs@MW4pp'
        );
    }

    require_once 'abstracts/class-plugin.php';

    require_once 'deps/i18n/wpct-i18n.php';

    require_once 'includes/class-menu.php';
    require_once 'includes/class-settings.php';
    require_once 'includes/class-http-client.php';
    require_once 'includes/class-http-backend.php';
    require_once 'includes/class-jwt.php';
    require_once 'includes/class-rest-settings-controller.php';
    require_once 'includes/class-rest-auth-controller.php';
    require_once 'includes/http-requests.php';

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
        public function construct(...$args)
        {
            parent::construct(...$args);
            REST_Auth_Controller::setup();

            $this->wp_hooks();
            $this->custom_hooks();
        }

        /**
         * Bind plugin to wp hooks.
         */
        private function wp_hooks()
        {
            // Enqueue plugin admin client scripts
            add_action('admin_enqueue_scripts', function ($admin_page) {
                $this->admin_enqueue_scripts($admin_page);
            });
        }

        /**
         * Adds plugin custom filters.
         */
        private function custom_hooks()
        {
            // Gets a new backend instance.
            add_filter(
                'http_bridge_backend',
                function ($default, $name) {
                    return new Http_Backend($name);
                },
                10,
                2
            );

            // Gets all configured backend instances.
            add_filter(
                'http_bridge_backends',
                function () {
                    return Http_Backend::get_backends();
                },
                10
            );
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
                $this->textdomain(),
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
                $this->textdomain(),
                $this->textdomain(),
                plugin_dir_path(__FILE__) . 'languages'
            );

            wp_enqueue_style('wp-components');
        }
    }
}

HTTP_Bridge::setup();
