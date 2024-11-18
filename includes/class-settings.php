<?php

namespace WPCT_HTTP;

use WPCT_ABSTRACT\Settings as BaseSettings;

/**
 * Plugin settings.
 *
 * @since 1.0.0
 */
class Settings extends BaseSettings
{
    /**
     * Register plugin settings.
     *
     * @since 2.0.0
     */
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
                'backends' => [
                    [
                        'name' => 'ERP',
                        'base_url' => 'https://erp.' . $url['host'],
                        'headers' => [
							[
								'name' => 'Authorization',
                                'value' => 'Bearer <backend-api-token>'
							]
						],
                    ],
                ],
            ],
        );
    }
}
