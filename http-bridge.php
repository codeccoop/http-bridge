<?php

/**
 * Plugin Name:     HTTP Bridge
 * Plugin URI:      https://git.coopdevs.org/codeccoop/wp/plugins/bridges/http-bridge
 * Description:     Connect WP with backends over HTTP
 * Author:          codeccoop
 * Author URI:      https://www.codeccoop.org
 * License:         GPLv2 or later
 * License URI:     http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     http-bridge
 * Domain Path:     /languages
 * Version:         1.3.8
 */

namespace HTTP_BRIDGE;

use Exception;
use WPCT_ABSTRACT\Plugin;

use function WPCT_ABSTRACT\is_list;

if (!defined('ABSPATH')) {
    exit();
}

if (!class_exists('\HTTP_BRIDGE\HTTP_Bridge')) {
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

    if (is_file(plugin_dir_path(__FILE__) . 'abstracts/class-plugin.php')) {
        include_once plugin_dir_path(__FILE__) . 'abstracts/class-plugin.php';
    }

    if (is_file(plugin_dir_path(__FILE__) . 'deps/i18n/wpct-i18n.php')) {
        include_once plugin_dir_path(__FILE__) . 'deps/i18n/wpct-i18n.php';
    }

    require_once 'includes/class-menu.php';
    require_once 'includes/class-settings-store.php';
    require_once 'includes/class-http-client.php';
    require_once 'includes/class-http-backend.php';
    require_once 'includes/class-jwt.php';
    require_once 'includes/class-rest-settings-controller.php';
    require_once 'includes/http-requests.php';

    /**
     * HTTP Bridge plugin.
     */
    class HTTP_Bridge extends Plugin
    {
        /**
         * Handles plugin's settings store class name.
         *
         * @var string $settings_class Plugins settings class name.
         */
        protected static $settings_class = '\HTTP_BRIDGE\SettingsStore';

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
            // REST_Auth_Controller::setup();

            self::wp_hooks();
            self::custom_hooks();
        }

        /**
         * Bind plugin to wp hooks.
         */
        private static function wp_hooks()
        {
            // Enqueue plugin admin client scripts
            add_action('admin_enqueue_scripts', static function ($admin_page) {
                self::admin_enqueue_scripts($admin_page);
            });
        }

        /**
         * Adds plugin custom filters.
         */
        private static function custom_hooks()
        {
            // Gets a new backend instance.
            add_filter(
                'http_bridge_backend',
                static function ($default, $name) {
                    try {
                        return new Http_Backend($name);
                    } catch (Exception) {
                        return null;
                    }
                },
                10,
                2
            );

            // Gets all configured backend instances.
            add_filter(
                'http_bridge_backends',
                static function ($backends) {
                    if (!is_list($backends)) {
                        $backends = [];
                    }

                    return array_merge($backends, Http_Backend::get_backends());
                },
                10,
                1
            );
        }

        /**
         * Enqueue admin client scripts
         *
         * @param string $admin_page Current admin page.
         */
        private static function admin_enqueue_scripts($admin_page)
        {
            $slug = self::slug();
            $version = self::version();
            if ('settings_page_' . $slug !== $admin_page) {
                return;
            }

            wp_enqueue_script(
                $slug . '-admin',
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
                $version,
                ['in_footer' => true]
            );

            wp_set_script_translations(
                $slug . '-admin',
                plugin_dir_path(__FILE__) . 'languages'
            );

            wp_enqueue_style('wp-components');
        }
    }
}

HTTP_Bridge::setup();
