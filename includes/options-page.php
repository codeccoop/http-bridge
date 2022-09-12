<?php

/**
 * Define menu page.
 */
 
function wpct_odoo_connect_add_admin_menu()
{

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
function wpct_odoo_connect_settings_init()
{

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

    add_settings_section(
        'wpct_odoo_connect_odooConnectCoordId_section',
        __('Mapping values', 'wpct_odoo_cood_id'),
        'wpct_odoo_cood_id_section_callback',
        'odooConnectSettingsPage'
    );

    add_settings_field(
        'wpct_odoo_connect_coord_id',
        __('Coord Company ID', 'wpct_odoo_cood_id'),
        'wpct_odoo_connect_coord_id_render',
        'odooConnectSettingsPage',
        'wpct_odoo_connect_odooConnectCoordId_section'
    );
    register_setting('odooConnectSettingsPage', 'odoo_forms_settings');
    add_settings_section(
        'Dropdown_odooConnectSettingsPage_section',
        __('Map existing forms with the ERP endpoints', 'dropdown'),
        'Odoo_forms_settings_section_callback',
        'odooConnectSettingsPage'
    );

    add_settings_field(
        'ce_source_creation_ce_proposal',
        __('Form - New Energy Community (ce_source_creation_ce_proposal)', 'dropdown'),
        'New_community_select_field_render',
        'odooConnectSettingsPage',
        'Dropdown_odooConnectSettingsPage_section'
    );
    add_settings_field(
        'ce_source_future_location_ce_info',
        __('Form - Interest in zone (ce_source_future_location_ce_info)', 'dropdown'),
        'Zone_interest_select_field_render',
        'odooConnectSettingsPage',
        'Dropdown_odooConnectSettingsPage_section'
    );
    add_settings_field(
        'general_newsletter_form',
        __('Form - General Newsletter', 'dropdown'),
        'General_newsletter_select_field_render',
        'odooConnectSettingsPage',
        'Dropdown_odooConnectSettingsPage_section'
    );

    register_setting('odooConnectSettingsPage', 'accions_energetiques_mapping_settings');
    add_settings_section('accionsEnergetiquesMapping_section',
    'Accións Energétiques mapping',
    'accionsEnergetiquesMapping_callback', 
    'odooConnectSettingsPage' );

    add_settings_field(
        'generacio',
        __('Generació renovable comunitaria id', 'text'),
        'generacioRenovableComunitariaMapping_render',
        'odooConnectSettingsPage',
        'accionsEnergetiquesMapping_section'
    );

    add_settings_field(
        'eficiencia',
        __('Eficiencia energètica id', 'text'),
        'eficienciaEnergeticaMapping_render',
        'odooConnectSettingsPage',
        'accionsEnergetiquesMapping_section'
    );

    add_settings_field(
        'mobilitat',
        __('Mobilitat sostenible id', 'text'),
        'mobilitatSostenibleMapping_render',
        'odooConnectSettingsPage',
        'accionsEnergetiquesMapping_section'
    );
    
    ## Add setting field for Formació ciutadana    
    add_settings_field(
        'formacio',
        __('Formació ciutadana id', 'text'),
        'formacioCiutadanaMapping_render',
        'odooConnectSettingsPage',
        'accionsEnergetiquesMapping_section'
    );

    ## Add seting field for Energia tèrmica i climatització
    add_settings_field(
        'termica',
        __('Energia tèrmica i climatització id', 'text'),
        'energiaTermicaIClimatitzacioMapping_render',
        'odooConnectSettingsPage',
        'accionsEnergetiquesMapping_section'
    );

    ## Add setting fields for Compres col·lectives, Subministrament d'energia 100% renovable and Agregació i flexibilitat de la demanda

    add_settings_field(
        'compres',
        __('Compres col·lectives id', 'text'),
        'compresCollectivesMapping_render',
        'odooConnectSettingsPage',
        'accionsEnergetiquesMapping_section'
    );

    add_settings_field(
        'subministrament',
        __('Subministrament d\'energia 100% renovable id', 'text'),
        'subministramentEnergiaRenovableMapping_render',
        'odooConnectSettingsPage',
        'accionsEnergetiquesMapping_section'
    );

    add_settings_field(
        'agregacio',
        __('Agregació i flexibilitat de la demanda id', 'text'),
        'agregacioIFlexibilitatDemandMapping_render',
        'odooConnectSettingsPage',
        'accionsEnergetiquesMapping_section'
    );
    
}

add_action('admin_init', 'wpct_odoo_connect_settings_init');


/**
 * Iterate Gravity Forms and extract the form IDs and names
 */
function iterate_forms($option_name)
{
    $options = get_option('odoo_forms_settings') ? get_option('odoo_forms_settings') : [];
    $selected = 'disabled ';
    if (!key_exists($option_name, $options) || !$options) {
        $selected .= 'selected';
        $options[$option_name] = '';
    }
    $result = GFAPI::get_forms();
    echo "<select name='odoo_forms_settings[" . $option_name . "]'>";
    echo '<option value="null" ' . $selected . '>Select a form</option>';
    foreach ($result as $key => $form) {
        echo '<option value="' . $result[$key]['id'] . '" ' . (($options[$option_name] ? $options[$option_name] : 'null') == $result[$key]['id']  ? 'selected' : '') . '>' . $form['title'] . '</option>';
    }
    echo "</select>";
}

/**
 * Render the forms
 */

function New_community_select_field_render()
{
    $option_name = 'ce_source_creation_ce_proposal';
    iterate_forms($option_name);
}

function Zone_interest_select_field_render()
{
    $option_name = 'ce_source_future_location_ce_info';
    iterate_forms($option_name);
}

function General_newsletter_select_field_render()
{
    $option_name = 'general_newsletter_form';
    iterate_forms($option_name);
}

function wpct_odoo_connect_textField_apiKey_render()
{

    $options = get_option('wpct_odoo_connect_settings') ? get_option('wpct_odoo_connect_settings') : [];
    $current_api_key = $options['wpct_odoo_connect_textField_apiKey'] ? $options['wpct_odoo_connect_textField_apiKey'] : '';
    echo "<input type='text' name='wpct_odoo_connect_settings[wpct_odoo_connect_textField_apiKey]' value='" . $current_api_key . "'> ";
}


function wpct_odoo_connect_coord_id_render()
{

    $options = get_option('wpct_odoo_connect_settings') ? get_option('wpct_odoo_connect_settings') : [];
    key_exists('wpct_odoo_connect_coord_id', $options) ? $coord_id = $options['wpct_odoo_connect_coord_id'] : $coord_id = '-1';
    echo "<input type='text' name='wpct_odoo_connect_settings[wpct_odoo_connect_coord_id]' value='" . $coord_id . "'>";
}

function generacioRenovableComunitariaMapping_render()
{

    $options = get_option('accions_energetiques_mapping_settings') ? get_option('accions_energetiques_mapping_settings') : [];
    key_exists('generacio', $options) ? $generacioRenovableComunitariaMapping = $options['generacio'] : $generacioRenovableComunitariaMapping = '';
    echo "<input type='text' name='accions_energetiques_mapping_settings[generacio]' value='" . $generacioRenovableComunitariaMapping . "'>";
}

function eficienciaEnergeticaMapping_render()
{

    $options = get_option('accions_energetiques_mapping_settings') ? get_option('accions_energetiques_mapping_settings') : [];
    key_exists('eficiencia', $options) ? $eficienciaEnergeticaMapping = $options['eficiencia'] : $eficienciaEnergeticaMapping = '';
    echo "<input type='text' name='accions_energetiques_mapping_settings[eficiencia]' value='" . $eficienciaEnergeticaMapping . "'>";
}

function mobilitatSostenibleMapping_render()
{

    $options = get_option('accions_energetiques_mapping_settings') ? get_option('accions_energetiques_mapping_settings') : [];
    key_exists('mobilitat', $options) ? $mobilitatSostenibleMapping = $options['mobilitat'] : $mobilitatSostenibleMapping = '';
    echo "<input type='text' name='accions_energetiques_mapping_settings[mobilitat]' value='" . $mobilitatSostenibleMapping . "'>";
}

function formacioCiutadanaMapping_render()
{

    $options = get_option('accions_energetiques_mapping_settings') ? get_option('accions_energetiques_mapping_settings') : [];
    key_exists('formacio', $options) ? $formacioCiutadanaMapping = $options['formacio'] : $formacioCiutadanaMapping = '';
    echo "<input type='text' name='accions_energetiques_mapping_settings[formacio]' value='" . $formacioCiutadanaMapping . "'>";
}

function energiaTermicaIClimatitzacioMapping_render()
{

    $options = get_option('accions_energetiques_mapping_settings') ? get_option('accions_energetiques_mapping_settings') : [];
    key_exists('termica', $options) ? $energiaTermicaIClimatitzacioMapping = $options['termica'] : $energiaTermicaIClimatitzacioMapping = '';
    echo "<input type='text' name='accions_energetiques_mapping_settings[termica]' value='" . $energiaTermicaIClimatitzacioMapping . "'>";
}

function compresCollectivesMapping_render()
{

    $options = get_option('accions_energetiques_mapping_settings') ? get_option('accions_energetiques_mapping_settings') : [];
    key_exists('compres', $options) ? $compresCollectivesMapping = $options['compres'] : $compresCollectivesMapping = '';
    echo "<input type='text' name='accions_energetiques_mapping_settings[compres]' value='" . $compresCollectivesMapping . "'>";
}

function subministramentEnergiaRenovableMapping_render()
{

    $options = get_option('accions_energetiques_mapping_settings') ? get_option('accions_energetiques_mapping_settings') : [];
    key_exists('subministrament', $options) ? $subministramentEnergiaRenovableMapping = $options['subministrament'] : $subministramentEnergiaRenovableMapping = '';
    echo "<input type='text' name='accions_energetiques_mapping_settings[subministrament]' value='" . $subministramentEnergiaRenovableMapping . "'>";
}

function agregacioIFlexibilitatDemandMapping_render()
{

    $options = get_option('accions_energetiques_mapping_settings') ? get_option('accions_energetiques_mapping_settings') : [];
    key_exists('agregacio', $options) ? $agregacioIFlexibilitatDemandMapping = $options['agregacio'] : $agregacioIFlexibilitatDemandMapping = '';
    echo "<input type='text' name='accions_energetiques_mapping_settings[agregacio]' value='" . $agregacioIFlexibilitatDemandMapping . "'>";
}


/**
 * Callbacks for the settings sections
 */
function wpct_odoo_connect_settings_section_callback()
{
    echo __('Copy here your Odoo API key', 'wpct_odoo_connect');
}

function wpct_odoo_cood_id_section_callback()
{
    echo __('Map values from WP to backend settings', 'wpct_odoo_cood_id');
}

function Odoo_forms_settings_section_callback()
{
    echo __('Asign the utm.source field to each form', 'dropdown');
}

function accionsEnergetiquesMapping_callback()
{
    echo __('Map values from Accions Energétiques select to backend settings', 'accionsEnergetiquesMapping');
}

/**
 * Paint the settings page
 */
function wpct_odoo_connect_options_page()
{
    echo "<form action='options.php' method='post'>";
    echo "<h2>WPCT Odoo Connect</h2>";
    settings_fields('odooConnectSettingsPage');
    do_settings_sections('odooConnectSettingsPage');
    submit_button();
    echo "</form>";
}
