<?php

namespace WPCT_HB;

require_once 'class-base-settings.php';

class Settings extends BaseSettings
{
    public $group_name = 'wpct-http-backend';

    public function register()
    {
        $url = parse_url(get_site_url());
        $setting_name = $this->group_name . '_general';
        $this->register_setting(
            $setting_name,
            [
                'base_url' => 'https://backend.' . $url['host'],
                'api_key' => '123456789',
            ]
        );

        $this->register_field('base_url', $setting_name);
        $this->register_field('api_key', $setting_name);
    }
}
