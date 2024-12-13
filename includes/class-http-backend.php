<?php

namespace HTTP_BRIDGE;

use Exception;

if (!defined('ABSPATH')) {
    exit();
}

/**
 * HTTP backend connexión.
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
        return array_map(function ($backend_data) {
            return new HTTP_Backend($backend_data['name']);
        }, Settings::get_setting('http-bridge', 'general')->backends);
    }

    /**
     * Store backend data
     */
    public function __construct($name)
    {
        $this->data = $this->load_data($name);
        if (!$this->data) {
            throw new Exception(
                "Http backend error: Unkown backend with name {$name}"
            );
        }
    }

    /**
     * Backend data getter.
     *
     * @return array|null Backen data.
     */
    private function load_data($name)
    {
        $backends = Settings::get_setting('http-bridge', 'general')->backends;
        foreach ($backends as $backend) {
            if ($backend['name'] === $name) {
                return $backend;
            }
        }

        return null;
    }

    /**
     * Intercepts class attributes accesses and lookup on backend data.
     *
     * @param string $attr Attribute name.
     *
     * @return mixed Attribute value or null.
     */
    public function __get($attr)
    {
        switch ($attr) {
            case 'headers':
                return $this->headers();
            case 'content_type':
                return $this->content_type();
            default:
                if (isset($this->data[$attr])) {
                    return $this->data[$attr];
                }
        }
    }

    /**
     * Gets backend default headers.
     *
     * @return array $headers Backend headers.
     */
    private function headers()
    {
        $headers = [];
        foreach ($this->headers as $header) {
            $headers[trim($header['name'])] = trim($header['value']);
        }

        return apply_filters('http_bridge_backend_headers', $headers, $this);
    }

    /**
     * Gets backend default request body content type encoding schema.
     *
     * @return string|null Encoding schema.
     */
    private function content_type()
    {
        $headers = $this->headers();
        return Http_Client::get_content_type($headers);
    }

    /**
     * Gets backend absolute URL.
     *
     * @param string $path URL relative path.
     *
     * @return string $url Absolute URL.
     */
    public function url($path = '')
    {
        $parsed = parse_url($path);
        if (!isset($parsed['path'])) {
            return $this->base_url;
        } else {
            $path = $parsed['path'];
        }

        $url =
            preg_replace('/\/+$/', '', $this->base_url) .
            '/' .
            preg_replace('/^\/+/', '', $path);

        return apply_filters('http_bridge_backend_url', $url, $this);
    }

    /**
     * Performs a GET HTTP request to the backend.
     *
     * @param string $endpoint Target backend endpoint as relative path.
     * @param array $params URL query params.
     * @param array $headers Additional HTTP headers.
     *
     * @return array|WP_Error Request response.
     */
    public function get($endpoint, $params = [], $headers = [])
    {
        $url = $this->url($endpoint);
        $headers = array_merge($this->headers(), (array) $headers);
        return http_bridge_get($url, $params, $headers);
    }

    /**
     * Performs a POST HTTP request to the backend.
     *
     * @param string $endpoint Target backend endpoint as relative path.
     * @param array $params URL query params.
     * @param array $headers Additional HTTP headers.
     *
     * @return array|WP_Error Request response.
     */
    public function post($endpoint, $data = [], $headers = [], $files = [])
    {
        $url = $this->url($endpoint);
        $headers = array_merge($this->headers(), (array) $headers);
        return http_bridge_post($url, $data, $headers, $files);
    }

    /**
     * Performs a PUT HTTP request to the backend.
     *
     * @param string $endpoint Target backend endpoint as relative path.
     * @param array $params URL query params.
     * @param array $headers Additional HTTP headers.
     *
     * @return array|WP_Error Request response.
     */
    public function put($endpoint, $data = [], $headers = [], $files = [])
    {
        $url = $this->url($endpoint);
        $headers = array_merge($this->headers(), (array) $headers);
        return http_bridge_put($url, $data, $headers, $files);
    }

    /**
     * Performs a DELETE HTTP request to the backend.
     *
     * @param string $endpoint Target backend endpoint as relative path.
     * @param array $params URL query params.
     * @param array $headers Additional HTTP headers.
     *
     * @return array|WP_Error Request response.
     */
    public function delete($endpoint, $params = [], $headers = [])
    {
        $url = $this->url($endpoint);
        $headers = array_merge($this->headers(), (array) $headers);
        return http_bridge_delete($url, $params, $headers);
    }
}
