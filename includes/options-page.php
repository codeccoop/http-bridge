<?php


function wpct_odoo_connect_add_admin_menu(  ) { 

    add_options_page(
        'wpct_odoo_connect',
        'WPCT Odoo Connect',
        'manage_options',
        'wpct_odoo_connect',
        'wpct_odoo_connect_options_page'
    );

}
add_action( 'admin_menu', 'wpct_odoo_connect_add_admin_menu' );


function wpct_odoo_connect_settings_init(  ) { 

    register_setting( 'odooConnectSettingsPage', 'wpct_odoo_connect_settings' );

    add_settings_section(
        'wpct_odoo_connect_odooConnectSettingsPage_section', 
        __( 'API Key', 'wpct_odoo_connect' ), 
        'wpct_odoo_connect_settings_section_callback', 
        'odooConnectSettingsPage'
    );

    add_settings_field( 
        'wpct_odoo_connect_textField_apiKey', 
        __( 'API Key', 'wpct_odoo_connect' ), 
        'wpct_odoo_connect_textField_apiKey_render', 
        'odooConnectSettingsPage', 
        'wpct_odoo_connect_odooConnectSettingsPage_section' 
    );


}
add_action( 'admin_init', 'wpct_odoo_connect_settings_init' );


function wpct_odoo_connect_textField_apiKey_render(  ) {

    $options = get_option( 'wpct_odoo_connect_settings' );
    ?>
    <input type='text' name='wpct_odoo_connect_settings[wpct_odoo_connect_textField_apiKey]' value="<?php echo $options['wpct_odoo_connect_textField_apiKey']; ?>">
    <?php

}


function wpct_odoo_connect_settings_section_callback(  ) { 

    echo __( 'Copy here your Odoo API key', 'wpct_odoo_connect' );

}


function wpct_odoo_connect_options_page(  ) { 

    ?>
    <form action='options.php' method='post'>

        <h2>WPCT Odoo Connect</h2>

        <?php
        settings_fields( 'odooConnectSettingsPage' );
        do_settings_sections( 'odooConnectSettingsPage' );
        submit_button();
        ?>

    </form>
    <?php

}