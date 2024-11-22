<?php

namespace HTTP_BRIDGE;

use WPCT_ABSTRACT\Menu as BaseMenu;

if (!defined('ABSPATH')) {
    exit();
}

/**
 * Plugin menu class.
 */
class Menu extends BaseMenu
{
    /**
     * Handle plugin settings class name.
     *
     * @var string $settings_class Settings class name.
     */
    protected static $settings_class = '\HTTP_BRIDGE\Settings';

    /**
     * Render plugin menu page.
     */
    protected function render_page($echo = true)
    {
        printf(
            '<div class="wrap" id="http-bridge">%s</div>',
            esc_html__('Loading', 'http-bridge')
        );
    }
}
