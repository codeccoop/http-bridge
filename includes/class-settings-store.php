<?php

namespace HTTP_BRIDGE;

use WPCT_ABSTRACT\Settings_Store as Base_Settings_Store;

if (!defined('ABSPATH')) {
    exit();
}

/**
 * Plugin settings store.
 */
class Settings_Store extends Base_Settings_Store
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
            'wpct_validate_setting',
            static function ($value, $setting) {
                return self::validate_setting($value, $setting);
            },
            10,
            2
        );
    }

    /**
     * Registers plugin settings.
     */
    public static function config()
    {
        $url = wp_parse_url(get_site_url());

        return [
            [
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
                ],
            ],
        ];
    }

    /**
     * Callback to the `wpct_sanitize_setting` filter. Sanitizes and validates
     * plugin setting data before database updates.
     *
     * @param array $data Setting data.
     * @param Setting $setting Setting object instance.
     *
     * @return array Sanitized and validated data.
     */
    private static function validate_setting($data, $setting)
    {
        if ($setting->group() !== self::group()) {
            return $data;
        }

        $name = $setting->name();
        switch ($name) {
            case 'general':
                $data = self::validate_general($data);
                break;
        }

        return $data;
    }

    /**
     * Validates plugin's general setting data.
     *
     * @param array $data Setting data.
     *
     * @return array Validated data.
     */
    public static function validate_general($data)
    {
        $data['backends'] = self::validate_backends($data['backends']);
        return $data;
    }

    /**
     * Validate plugin's backend settings.
     *
     * @param array $backends List with backend settings.
     *
     * @return array Filtered by validity backend settings list.
     */
    public static function validate_backends($backends)
    {
        $backends = array_filter((array) $backends, static function ($backend) {
            return filter_var($backend['base_url'], FILTER_VALIDATE_URL) &&
                is_array($backend['headers']);
        });

        $names = array_unique(
            array_map(function ($backend) {
                return $backend['name'];
            }, $backends)
        );

        $uniques = [];
        foreach ($backends as $backend) {
            if (in_array($backend['name'], $names, true)) {
                $uniques[] = $backend;
                $index = array_search($backend['name'], $names);
                unset($names[$index]);
            }
        }

        return $uniques;
    }
}
