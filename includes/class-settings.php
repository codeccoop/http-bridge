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
                'backends' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'base_url' => ['type' => 'string'],
                            'headers' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'backends' => [
                    [
                        'name' => 'ERP',
                        'base_url' => 'https://erp.' . $url['host'],
                        'headers' => ['Authorization: Bearer <backend-api-token>'],
                    ],
                ],
            ],
        );
    }
}
