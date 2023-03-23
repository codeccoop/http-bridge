<?php

/**
 * Define menu page.
 */
add_action('admin_menu', 'wpct_oc_add_admin_menu');
function wpct_oc_add_admin_menu()
{
    add_options_page(
        'WPCT Odoo Connect',
        'WPCT Odoo Connect',
        'manage_options',
        'wpct_oc_menu',
        'wpct_oc_options_page'
    );
}

/**
 * Paint the settings page
 */
function wpct_oc_options_page()
{
    echo '<div class="wrap">';
    echo '<h1>WPCT Odoo Connect</h1>';
    echo '<form action="options.php" method="post">';
    settings_fields('wpct_oc_options');
    do_settings_sections('wpct_oc_options');
    submit_button();
    echo '</form>';
    echo '</div>';
}

/**
 * Define settings.
 */
add_action('admin_init', 'wpct_oc_settings_init');
function wpct_oc_settings_init()
{
    register_setting(
        'wpct_oc_options',
        'wpct_oc_api_key',
        array(
            'type' => 'string',
            'description' => 'Odoo API key',
            'show_in_rest' => false,
        )
    );
    add_settings_section(
        'wpct_oc_api_settings',
        __('Odoo API Settings', 'wpct_odoo_connect'),
        'wpct_oc_settings_section_callback',
        'wpct_oc_options'
    );
    add_settings_field(
        'wpct_oc_api_key',
        __('API Key', 'wpct_odoo_connect'),
        'wpct_oc_api_key_render',
        'wpct_oc_options',
        'wpct_oc_api_settings'
    );
}

/**
 * Render the forms
 */
function wpct_oc_api_key_render()
{
    echo "<input type='text' name='wpct_oc_api_key' value='" . wpct_oc_get_api_key() . "'> ";
}


/**
 * Callbacks for the settings sections
 */
function wpct_oc_settings_section_callback()
{
    echo __('Copy here your Odoo API key', 'wpct_odoo_connect');
}

/**
 * API Key getter
 */
function wpct_oc_get_api_key()
{
    return get_option('wpct_oc_api_key') ? get_option('wpct_oc_api_key') : '';
}
