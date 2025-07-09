<?php

namespace HTTP_BRIDGE;

use WPCT_PLUGIN\Menu as BaseMenu;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Plugin menu class.
 */
class Menu extends BaseMenu
{
    /**
     * Renders the plugin menu page.
     */
    protected static function render_page($echo = true)
    {
        printf(
            '<div class="wrap" id="http-bridge">%s</div>',
            esc_html__('Loading', 'http-bridge')
        );
    }
}
