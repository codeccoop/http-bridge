<?php

function wpct_oc_user_locale()
{
    $locale = apply_filters('wpml_current_language', null);
    if (!$locale) {
        $locale = WPCT_OC_DEFAULT_LOCALE;
    }
    return $locale;
}

function wpct_oc_accept_language_header()
{
    $locale = wpct_oc_user_locale();
    return $locale . ', es;q=0.5';
}
