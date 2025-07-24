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
 * Version:         1.3.15
 */

namespace HTTP_BRIDGE;

use WPCT_PLUGIN\Plugin;

if (!defined('ABSPATH')) {
    exit();
}

if (!class_exists('\HTTP_BRIDGE\Http_Bridge')) {
    if (is_file(plugin_dir_path(__FILE__) . 'common/class-plugin.php')) {
        include_once plugin_dir_path(__FILE__) . 'common/class-plugin.php';
    }

    if (is_file(plugin_dir_path(__FILE__) . 'deps/i18n/wpct-i18n.php')) {
        include_once plugin_dir_path(__FILE__) . 'deps/i18n/wpct-i18n.php';
    }

    require_once 'includes/class-menu.php';
    require_once 'includes/class-settings-store.php';
    require_once 'includes/class-http-client.php';
    require_once 'includes/class-backend.php';
    require_once 'includes/class-credential.php';
    require_once 'includes/class-jwt.php';
    require_once 'includes/class-rest-settings-controller.php';
    require_once 'includes/http-requests.php';

    /**
     * HTTP Bridge plugin.
     */
    class Http_Bridge extends Plugin
    {
        /**
         * Handles plugin's settings store class name.
         *
         * @var string $settings_class Plugins settings class name.
         */
        protected const store_class = '\HTTP_BRIDGE\Settings_Store';

        /**
         * Plugin menu class name handle.
         *
         * @var string $menu_class Menu class name.
         */
        protected const menu_class = '\HTTP_BRIDGE\Menu';

        /**
         * Setup the rest controller and bind wp hooks.
         */
        public function construct(...$args)
        {
            parent::construct(...$args);

            add_filter(
                'http_bridge_backends',
                static function ($backends) {
                    if (!wp_is_numeric_array($backends)) {
                        $backends = [];
                    }

                    $setting = self::setting('general');
                    if (!$setting) {
                        return $backends;
                    }

                    foreach ($setting->backends ?: [] as $data) {
                        $backends[] = new Backend($data);
                    }

                    return $backends;
                },
                10,
                1
            );

            add_filter(
                'http_bridge_credentials',
                static function ($credentials) {
                    if (!wp_is_numeric_array($credentials)) {
                        $credentials = [];
                    }

                    $setting = self::setting('general');
                    if (!$setting) {
                        return $credentials;
                    }

                    foreach ($setting->credentials ?: [] as $data) {
                        $credentials[] = new Credential($data);
                    }

                    return $credentials;
                },
                10,
                1
            );
        }
    }
}

Http_Bridge::setup();
