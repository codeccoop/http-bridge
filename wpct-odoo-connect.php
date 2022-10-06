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

// Plugin dependencies.
// if (!class_exists('GFFormsModel') || !class_exists('GF_Webhooks')) {
//     add_action('admin_notices', 'wpct_forms_admin_notices');
//     return;
// }

// Options PAGE
require_once "includes/options-page.php";

// set API KEY on Odoo requests
function wpct_forms_set_headers($request_headers, $feed, $entry, $form){
    $ocSettings = get_option("wpct_odoo_connect_settings");
    $request_headers['API-KEY'] = $ocSettings['wpct_odoo_connect_textField_apiKey'];
    return $request_headers;
}
