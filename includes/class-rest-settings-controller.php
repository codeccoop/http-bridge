<?php

namespace HTTP_BRIDGE;

use WPCT_ABSTRACT\REST_Settings_Controller as Base_Controller;

if (!defined('ABSPATH')) {
    exit();
}

/**
 * REST API Controller.
 */
class REST_Settings_Controller extends Base_Controller
{
    /**
     * Handle REST API controller namespace.
     *
     * @var string $namespace REST API namespace.
     */
    protected static $namespace = 'wp-bridges';

    /**
     * Handle REST API controller namespace version.
     *
     * @var int $version REST API namespace version.
     */
    protected static $version = 1;

    /**
     * Handle plugin settings names.
     *
     * @var array<string> $settings Plugin settings names list.
     */
    protected static $settings = ['general'];
}
