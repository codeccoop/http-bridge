<?php

/**
 * Define menu page.
 */
 
function wpct_odoo_connect_add_admin_menu(){

    add_options_page(
        'wpct_odoo_connect',
        'WPCT Odoo Connect',
        'manage_options',
        'wpct_odoo_connect',
        'wpct_odoo_connect_options_page'
    );
}
add_action('admin_menu', 'wpct_odoo_connect_add_admin_menu');

/**
 * Define settings.
 */
function wpct_odoo_connect_settings_init(){

    register_setting('odooConnectSettingsPage', 'wpct_odoo_connect_settings');

    add_settings_section(
        'wpct_odoo_connect_odooConnectSettingsPage_section',
        __('API Key', 'wpct_odoo_connect'),
        'wpct_odoo_connect_settings_section_callback',
        'odooConnectSettingsPage'
    );

    add_settings_field(
        'wpct_odoo_connect_textField_apiKey',
        __('API Key', 'wpct_odoo_connect'),
        'wpct_odoo_connect_textField_apiKey_render',
        'odooConnectSettingsPage',
        'wpct_odoo_connect_odooConnectSettingsPage_section'
    );
}

add_action('admin_init', 'wpct_odoo_connect_settings_init');

/**
 * Render the forms
 */
function wpct_odoo_connect_textField_apiKey_render(){

    $options = get_option('wpct_odoo_connect_settings') ? get_option('wpct_odoo_connect_settings') : [];
    $current_api_key = $options['wpct_odoo_connect_textField_apiKey'] ? $options['wpct_odoo_connect_textField_apiKey'] : '';
    echo "<input type='text' name='wpct_odoo_connect_settings[wpct_odoo_connect_textField_apiKey]' value='" . $current_api_key . "'> ";
}


/**
 * Callbacks for the settings sections
 */
function wpct_odoo_connect_settings_section_callback(){
    echo __('Copy here your Odoo API key', 'wpct_odoo_connect');
}

/**
 * Paint the settings page
 */
function wpct_odoo_connect_options_page(){
    echo "<form action='options.php' method='post'>";
    echo "<h2>WPCT Odoo Connect</h2>";
    settings_fields('odooConnectSettingsPage');
    do_settings_sections('odooConnectSettingsPage');
    submit_button();
    echo "</form>";
}
