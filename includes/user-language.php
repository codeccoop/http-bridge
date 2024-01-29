<?php

function wpct_hb_user_locale()
{
    $locale = apply_filters('wpct_st_current_language', null, 'locale');
    if ($locale) return $locale;

    return get_locale();
}

function wpct_hb_accept_language_header()
{
    $locale = wpct_hb_user_locale();
    return $locale . ', ca;q=0.9';
}
