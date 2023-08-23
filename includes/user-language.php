<?php

function wpct_oc_user_locale()
{
    $language = apply_filters('wpml_post_language_details', null);
    if ($language) {
        $locale = $language['locale'];
    } else {
        $locale = WPCT_OC_DEFAULT_LOCALE;
    }

    return $locale;
}

function wpct_oc_accept_language_header()
{
    $locale = wpct_oc_user_locale();
    return $locale . ', ca;q=0.9';
}
