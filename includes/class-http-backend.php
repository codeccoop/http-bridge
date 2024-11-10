<?php

namespace WPCT_HTTP;

use Exception;

/**
 * HTTP Backend
 *
 * @since 3.0.0
 */
class Http_Backend
{
    /**
     * Handle backend data.
     *
     * @since 3.0.0
     */
    private $data = null;

    /**
     * Store backend data
     *
     * @since 3.0.0
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
     * @since 3.0.0
     */
    private function get_backend($name)
    {
        $backends = Settings::get_setting('wpct-http-bridge', 'general', 'backends');
        foreach ($backends as $backend) {
            if ($backend['name'] === $name) {
                return $backend;
            }
        }

        return null;
    }

    /**
     * Intercept class gets and lookup on backend data.
     *
     * @since 3.0.0
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
     * @since 3.0.0
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
     * @since 3.0.0
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
add_filter('wpct_http_backend', function ($null, $name) {
    return new Http_Backend($name);
}, 10, 2);
