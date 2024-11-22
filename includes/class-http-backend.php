<?php

namespace HTTP_BRIDGE;

use Exception;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * HTTP Backend
 */
class Http_Backend
{
    /**
     * Handle backend data.
     *
     * @var array|null $data Backend data.
     */
    private $data = null;

    /**
     * Backends data getter.
     *
     * @return array $backends Backends data.
     */
    public static function get_backends()
    {
        return Settings::get_setting('http-bridge', 'general', 'backends');
    }

    /**
     * Store backend data
     */
    public function __construct($name)
    {
        $this->data = $this->get_backend($name);
        if (!$this->data) {
            throw new Exception("Http backend error: Unkown backend with name {$name}");
        }
    }

    /**
     * Backend data getter.
     *
     * @return array|null $backend Backen data.
     */
    private function get_backend($name)
    {
        $backends = static::get_backends();
        foreach ($backends as $backend) {
            if ($backend['name'] === $name) {
                return $backend;
            }
        }

        return null;
    }

    /**
     * Intercept class gets and lookup on backend data.
     */
    public function __get($attr)
    {
        if (isset($this->data[$attr])) {
            return $this->data[$attr];
        }

        return null;
    }

    /**
     * Get backend absolute URL.
     *
     * @param string $path URL relative path.
     * @return string $url Absolute URL.
     */
    public function get_endpoint_url($path)
    {
        $url_data = parse_url($path);
        if (isset($url_data['scheme'])) {
            return $path;
        }

        $base_url = $this->base_url;
        return preg_replace('/\/$/', '', $base_url) . '/' . preg_replace('/^\//', '', $path);
    }

    /**
     * Get backend default headers.
     *
     * @return array $headers Backend headers.
     */
    public function get_headers()
    {
        $headers = [];
        foreach ($this->headers as $header) {
            $headers[strtolower(trim($header['name']))] = trim($header['value']);
        }

        return $headers;
    }
}

// Get new backend instance.
add_filter('http_bridge_backend', function ($default, $name) {
    return new Http_Backend($name);
}, 10, 2);

add_filter('http_bridge_backends', function () {
    return Http_Backend::get_backends();
}, 10);
