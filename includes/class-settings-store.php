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
    protected const rest_controller_class = '\HTTP_BRIDGE\REST_Settings_Controller';

    /**
     * Registers plugin settings.
     */
    protected function construct(...$args)
    {
        parent::construct(...$args);

        add_filter(
            'wpct_plugin_register_settings',
            function ($settings, $group) {
                if ($group !== Http_Bridge::slug()) {
                    return $settings;
                }

                $settings[] = [
                    'name' => 'general',
                    'properties' => [
                        'backends' => [
                            'type' => 'array',
                            'items' => Backend::schema(),
                            'default' => [],
                        ],
                        'credentials' => [
                            'type' => 'array',
                            'items' => Credential::schema(),
                            'default' => [],
                        ],
                    ],
                    'required' => ['backends', 'credentials'],
                    'default' => [
                        'backends' => [],
                        'credentials' => [],
                    ],
                ];

                return $settings;
            },
            9,
            2
        );

        self::ready(static function ($store) {
            $store::use_setter(
                'general',
                static function ($data) {
                    $uniques = [];
                    $backends = [];

                    foreach ($data['backends'] ?? [] as $backend) {
                        if (!in_array($backend['name'], $uniques, true)) {
                            $uniques[] = $backend['name'];
                            $backends[] = $backend;
                        }
                    }

                    $data['backends'] = $backends;

                    $uniques = [];
                    $credentials = [];

                    foreach ($data['credentials'] ?? [] as $credential) {
                        if (!in_array($credential['name'], $uniques, true)) {
                            $uniques[] = $credential['name'];
                            $credentials[] = $credential;
                        }
                    }

                    $data['credentials'] = $credentials;

                    return $data;
                },
                9
            );
        });
    }
}
