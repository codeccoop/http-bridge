<?php

namespace WPCT_HTTP;

class Settings extends Abstract\Settings
{
    public function register()
    {
        $url = parse_url(get_site_url());
        $setting_name = $this->group_name . '_general';
        $this->register_setting(
            $setting_name,
            [
                'base_url' => 'http://example.' . $url['host'],
                'api_key' => 'backend-api-key'
            ],
        );

        $this->register_field('base_url', $setting_name);
        $this->register_field('api_key', $setting_name);
    }
}
