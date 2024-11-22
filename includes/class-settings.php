<?php

namespace HTTP_BRIDGE;

use WPCT_ABSTRACT\Settings as BaseSettings;

if (!defined('ABSPATH')) {
    exit();
}

/**
 * Plugin settings.
 */
class Settings extends BaseSettings
{
    /**
     * Register plugin settings.
     */
    public function register()
    {
        $url = parse_url(get_site_url());

        // Register general settings
        $this->register_setting(
            'general',
            [
                'whitelist' => ['type' => 'boolean'],
                'backends' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'base_url' => ['type' => 'string'],
                            'headers' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'name' => ['type' => 'string'],
                                        'value' => ['type' => 'string'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'whitelist' => false,
                'backends' => [
                    [
                        'name' => 'ERP',
                        'base_url' => 'https://erp.' . $url['host'],
                        'headers' => [
                            [
                                'name' => 'Authorization',
                                'value' => 'Bearer <backend-api-token>',
                            ],
                        ],
                    ],
                ],
            ]
        );
    }
}
