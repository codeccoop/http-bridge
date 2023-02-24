<?php

function wpct_oc_user_locale()
{
    $locale = apply_filters('wpml_current_language', null);
    if (!$locale) {
        $locale = 'ca';
    }
    return $locale;
}

function wpct_oc_accept_language_header()
{
    $locale = wpct_oc_user_locale();
    return $locale . ', ca;q=0.9, es;q=0.8, en;1=0.7';
}
