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
        'wpct_oc',
        'wpct_oc_page_render'
    );
}

/**
 * Paint the settings page
 */
function wpct_oc_page_render()
{
    echo '<div class="wrap">';
    echo '<h1>WPCT Odoo Connect</h1>';
    echo '<form action="options.php" method="post">';
    settings_fields('wpct_oc');
    do_settings_sections('wpct_oc');
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
        'wpct_oc',
        'wpct_oc_base_url',
        array(
            'type' => 'string',
            'description' => 'Odoo Base URL',
            'show_in_rest' => false,
        )
    );
    register_setting(
        'wpct_oc',
        'wpct_oc_api_key',
        array(
            'type' => 'string',
            'description' => 'Odoo API key',
            'show_in_rest' => false,
        )
    );
    add_settings_section(
        'wpct_oc_section',
        __('Odoo API Settings', 'wpct-odoo-connect'),
        'wpct_oc_section_callback',
        'wpct_oc'
    );
    add_settings_field(
        'base_url',
        __('Odoo Base URL', 'wpct-odoo-connect'),
        fn () => wpct_oc_field_render('wpct_oc_base_url'),
        'wpct_oc',
        'wpct_oc_section'
    );
    add_settings_field(
        'api_key',
        __('API Key', 'wpct-odoo-connect'),
        fn () => wpct_oc_field_render('wpct_oc_api_key'),
        'wpct_oc',
        'wpct_oc_section'
    );
}

/**
 * Field renderers
 */
function wpct_oc_field_render($field)
{
    echo '<input type="text" name="' . $field . '" value="' . wpct_oc_option_getter($field) . '">';
}

/**
 * Callbacks for the settings sections
 */
function wpct_oc_section_callback()
{
    echo __('Configure Odoo API params', 'wpct-odoo-connect');
}

/**
 * Option getter
 */
function wpct_oc_option_getter($option)
{
    return get_option($option) ? get_option($option) : '';
}
