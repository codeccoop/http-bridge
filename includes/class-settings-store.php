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

        self::register_setting([
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
            ],
        ]);

        self::ready(static function ($store) {
            $store::use_setter('general', static function ($data) {
                $uniques = [];
                $backends = [];

                foreach ($data['backends'] ?? [] as $backend) {
                    if (!in_array($backend['name'], $uniques, true)) {
                        $uniques[] = $backend['name'];
                        $backends[] = $backend;
                    }
                }

                $data['backends'] = $backends;
                return $data;
            });
        });
    }
}
