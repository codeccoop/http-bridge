<?php

/**
 * Define menu page.
 */
add_action('admin_menu', 'wpct_hb_add_admin_menu');
function wpct_hb_add_admin_menu()
{
    add_options_page(
        'Wpct Http Backend',
        'Wpct Http Backend',
        'manage_options',
        'wpct_hb',
        'wpct_hb_page_render'
    );
}

/**
 * Paint the settings page
 */
function wpct_hb_page_render()
{
    echo '<div class="wrap">';
    echo '<h1>Wpct Http Backend</h1>';
    echo '<form action="options.php" method="post">';
    settings_fields('wpct_hb');
    do_settings_sections('wpct_hb');
    submit_button();
    echo '</form>';
    echo '</div>';
}

/**
 * Define settings.
 */
add_action('admin_init', 'wpct_hb_settings_init');
function wpct_hb_settings_init()
{
    register_setting(
        'wpct_hb',
        'wpct_hb_base_url',
        [
            'type' => 'string',
            'description' => 'Backend Base URL',
            'show_in_rest' => false,
        ]
    );

    register_setting(
        'wpct_hb',
        'wpct_hb_api_key',
        [
            'type' => 'string',
            'description' => 'Backend API key',
            'show_in_rest' => false,
        ]
    );

    add_settings_section(
        'wpct_hb_section',
        __('Backend API Settings', 'wpct-http-backend'),
        'wpct_hb_section_callback',
        'wpct_hb'
    );

    add_settings_field(
        'base_url',
        __('Backend Base URL', 'wpct-http-backend'),
        fn () => wpct_hb_field_render('wpct_hb_base_url'),
        'wpct_hb',
        'wpct_hb_section'
    );

    add_settings_field(
        'api_key',
        __('API Key', 'wpct-http-backend'),
        fn () => wpct_hb_field_render('wpct_hb_api_key'),
        'wpct_hb',
        'wpct_hb_section'
    );
}

/**
 * Field renderers
 */
function wpct_hb_field_render($field)
{
    echo '<input type="text" name="' . $field . '" value="' . wpct_hb_option_getter($field) . '">';
}

/**
 * Callbacks for the settings sections
 */
function wpct_hb_section_callback()
{
    echo __('Configure backend API params', 'wpct-http-backend');
}

/**
 * Option getter
 */
function wpct_hb_option_getter($option)
{
    return get_option($option) ? get_option($option) : '';
}
