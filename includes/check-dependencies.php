<?php

function wpct_oc_check_dependencies() {
    global $WPCT_OC_DEPENDENCIES;
    $missings = array();
    foreach ($WPCT_OC_DEPENDENCIES as $name => $file) {
        if (! wpct_oc_is_plugin_active($file)) {
            $missings[] = $name;
        }
    }

    return $missings;
}

function wpct_oc_is_plugin_active($plugin_main_file_path) {
    return in_array($plugin_main_file_path, wpct_oc_get_active_plugins());
}

function wpct_oc_get_active_plugins() {
    return apply_filters('active_plugins', get_option('active_plugins'));
}

function wpct_oc_dependency_warning($missings) {
    $list_items = "";
    foreach ($missings as $missing) {
        $list_items .= '<li>' . $missing . '</li>';
    }

    // TODO: Això dels estils i l'escript posat tot aquí a pelo és molt guarrero
    $style = '<style>
        #wpct-oc-warning {
            position: absolute;
            z-index: 1000;
            left: 0px;
            top: 0px;
            background-color: #ff0000;
            color: white;
            width: 100%;
        }

        .wpct-oc-warning__wrapper {
            margin: 0.5em 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .wpct-oc-warning__wrapper button {
            position: absolute;
            z-index: 10;
            right: 1rem;
            top: 1rem;
            cursor: pointer;
        }
    </style>';
    $html = '<div id="wpct-oc-warning">
        <div class="wpct-oc-warning__wrapper">
            <button>X</button>
            <p><b>WARNING:</b> WPCT Odoo Connect missing dependencies:</p>
            <ul>' . $list_items . '</ul>
        </div>
    </div>';
    $script = '<script>
        document.addEventListener("DOMContentLoaded", function () {
            var warning = document.getElementById("wpct-oc-warning");
            var btn = warning.getElementsByTagName("button")[0];
            btn.addEventListener("click", function (ev) {
                ev.preventDefault();
                warning.parentElement.removeChild(warning);
            });
        });
    </script>';

    echo $style . $html . $script;
}

function wpct_oc_notify_missing_dependencies() {
    if (! WP_DEBUG) {
        return;
    }

    $missings = wpct_oc_check_dependencies();
    if (sizeof($missings) > 0) {
        wpct_oc_dependency_warning($missings);
    }
}