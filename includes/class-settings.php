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
     * Handle plugin settings rest controller class name.
     *
     * @var string $rest_controller_class Settings REST Controller class name.
     */
    protected static $rest_controller_class = '\HTTP_BRIDGE\REST_Settings_Controller';

    public function construct(...$args)
    {
        parent::construct(...$args);

        add_filter(
            'wpct_sanitize_setting',
            function ($value, $setting) {
                return $this->sanitize_setting($value, $setting);
            },
            10,
            2
        );
    }

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
                        'additionalProperties' => false,
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'base_url' => ['type' => 'string'],
                            'headers' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'additionalProperties' => false,
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

    private function sanitize_setting($value, $setting)
    {
        if ($setting->group() !== $this->group()) {
            return $value;
        }

        $name = $setting->name();
        switch ($name) {
            case 'general':
                $value = self::validate_general($value);
                break;
        }

        return $value;
    }

    public static function validate_general($value)
    {
        $value['whitelist'] = (bool) $value['whitelist'];
        $value['backends'] = self::validate_backends($value['backends']);
        return $value;
    }

    public static function validate_backends($backends)
    {
        $unique_names = [];
        return array_map(
            function ($b) {
                $b['name'] = sanitize_text_field($b['name']);
                $b['base_url'] = sanitize_text_field($b['base_url']);
                for ($i = 0; $i < count($b['headers']); $i++) {
                    $b['headers'][$i]['name'] = sanitize_text_field(
                        $b['headers'][$i]['name']
                    );
                    $b['headers'][$i]['value'] = sanitize_text_field(
                        $b['headers'][$i]['value']
                    );
                }
                return $b;
            },
            array_filter((array) $backends, static function ($backend) use (
                $unique_names
            ) {
                return !in_array($backend['name'], $unique_names) &&
                    filter_var($backend['base_url'], FILTER_VALIDATE_URL) &&
                    is_array($backend['headers']);
            })
        );
    }
}
