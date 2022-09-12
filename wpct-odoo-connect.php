<?php
/**
 * Plugin Name:     Wpct Odoo Connect
 * Plugin URI:      https://git.coopdevs.org/coopdevs/website/wp/wp-plugins
 * Description:     Configure and connect to Odoo API
 * Author:          Coopdevs Treball SCCL
 * Author URI:      https://coopdevs.org
 * Text Domain:     wpct_odoo_connect
 * Domain Path:     /languages
 * Version:         0.1.0
 *
 * @package         Wpct_Odoo_Connect
 */

 // Check plugin dependencies.
if (!class_exists('GFAPI')) {
    add_action('admin_notices', 'wpct_odoo_connect_admin_notices');
    return;
}

function wpct_odoo_connect_admin_notices()
{
    echo '<div class="error"><p>' . __('WPCT Forms Maps Leads requires Gravity Forms and Gravity Forms Webhook', 'wpct-forms-map-lead') . '</p></div>';
}

require_once "includes/options-page.php";
