<?php

namespace WPCT_HTTP;

use WPCT_ABSTRACT\Settings as BaseSettings;

class Settings extends BaseSettings
{
    public function register()
    {
        $url = parse_url(get_site_url());
        $this->register_setting(
            'general',
            [
                'base_url' => [
                    'type' => 'string'
                ],
                'api_key' => [
                    'type' => 'string'
                ]
            ],
            [
                'base_url' => 'http://' . $url['host'],
                'api_key' => 'backend-api-key'
            ],
        );
    }
}
