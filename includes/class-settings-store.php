<?php

namespace HTTP_BRIDGE;

use WPCT_PLUGIN\Settings_Store as Base_Settings_Store;

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

    /**
     * Registers plugin settings.
     */
    protected function construct(...$args)
    {
        parent::construct(...$args);

        self::enqueue(static function ($settings) {
            $settings[] = [
                'name' => 'general',
                'properties' => [
                    'whitelist' => [
                        'type' => 'boolean',
                        'default' => false,
                    ],
                    'backends' => [
                        'type' => 'array',
                        'items' => Http_Backend::schema(),
                        'default' => [],
                    ],
                ],
                'required' => ['whitelist', 'backends'],
                'default' => [
                    'whitelist' => false,
                    'backends' => [],
                ]
            ];

            return $settings;
        });

        self::ready(static function ($store) {
            $store::use_setter('general', static function ($data) {
                return self::sanitize_general($data);
            });
        });
    }

    /**
     * Validates plugin's general setting data.
     *
     * @param array $data Setting data.
     *
     * @return array Validated data.
     */
    public static function sanitize_general($data)
    {
        $data['backends'] = self::sanitize_backends($data['backends']);
        return $data;
    }

    /**
     * Validate plugin's backend settings.
     *
     * @param array $backends List with backend settings.
     *
     * @return array Filtered by validity backend settings list.
     */
    public static function sanitize_backends($backends)
    {
        $sanitized = [];
        $names = [];
        foreach ($backends as $backend) {
            if (empty($backend['name'])) {
                continue;
            }

            if (in_array($backend['name'], $names, true)) {
                continue;
            }

            $backend['base_url'] = filter_var($backend['base_url'], FILTER_VALIDATE_URL);
            $sanitized[] = $backend;
        }

        return $sanitized;
    }
}
