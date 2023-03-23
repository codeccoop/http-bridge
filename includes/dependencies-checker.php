<?php

function wpct_oc_missing_dependencies()
{
    global $WPCT_OC_DEPENDENCIES;
    $missings = array();
    foreach ($WPCT_OC_DEPENDENCIES as $name => $file) {
        if (!wpct_oc_is_plugin_active($file)) {
            $missings[] = $name;
        }
    }

    return $missings;
}

function wpct_oc_is_plugin_active($plugin_main_file_path)
{
    return in_array($plugin_main_file_path, wpct_oc_get_active_plugins());
}

function wpct_oc_get_active_plugins()
{
    return apply_filters('active_plugins', get_option('active_plugins'));
}

function wpct_oc_admin_notices()
{
    $missings = wpct_oc_missing_dependencies();
    $list_items = array();
    foreach ($missings as $missing) {
        $list_items[] = '<li><b>' . $missing . '</b></li>';
    }

    $notice = '<div class="notice notice-warning" id="wpct-oc-warning">
       <p><b>WARNING:</b> WPCT Odoo Connect missing dependencies:</p>
       <ul style="list-style-type: decimal; padding-left: 1em;">' . implode(',', $list_items) . '</ul>
    </div>';

    echo $notice;
}

function wpct_oc_check_dependencies()
{
    $missings = wpct_oc_missing_dependencies();
    if (sizeof($missings) > 0) {
        add_action('admin_notices', 'wpct_oc_admin_notices');
    }
}

