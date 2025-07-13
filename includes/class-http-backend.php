<?php

namespace HTTP_BRIDGE;

use WP_Error;

if (!defined('ABSPATH')) {
    exit();
}

/**
 * HTTP backend connexión.
 */
class Http_Backend
{
    public static function schema()
    {
        return [
            '$schema' => 'http://json-schema.org/draft-04/schema#',
            'title' => 'http-backend-schema',
            'type' => 'object',
            'properties' => [
                'name' => [
                    'type' => 'string',
                    'minLength' => 1,
                ],
                'base_url' => [
                    'type' => 'string',
                    'format' => 'uri',
                ],
                'headers' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'value' => ['type' => 'string'],
                        ],
                        'required' => ['name', 'value'],
                        'additionalProperties' => false,
                    ],
                    'default' => [],
                ],
                'authentication' => [
                    'type' => 'object',
                    'properties' => [
                        'type' => [
                            'type' => 'string',
                            'enum' => [
                                'Basic',
                                'Token',
                                'Bearer',
                            ],
                        ],
                        'client_id' => [
                            'type' => 'string',
                            'default' => '',
                        ],
                        'client_secret' => [
                            'type' => 'string',
                            'minLength' => 1,
                        ],
                    ],
                    'required' => ['type', 'client_id', 'client_secret'],
                ],
            ],
            'required' => ['name', 'base_url', 'headers'],
            'additionalProperties' => false,
        ];
    }
    /**
     * Handle backend data.
     *
     * @var array|null $data Backend data.
     */
    private $data;

    /**
     * Store backend data
     */
    public function __construct($data)
    {
        $this->data = wpct_plugin_sanitize_with_schema($data, static::schema());
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
            case 'is_valid':
                return !is_wp_error($this->data);
            case 'headers':
                return $this->headers();
            case 'content_type':
                return $this->content_type();
            case 'authorization':
                return $this->authorization();
            default:
                if (!$this->is_valid) {
                    return;
                }

                return $this->data[$attr] ?? null;
        }
    }

    /**
     * Gets backend default headers.
     *
     * @return array $headers Backend headers.
     */
    private function headers()
    {
        if (!$this->is_valid) {
            return [];
        }

        $headers = [];
        foreach ($this->data['headers'] as $header) {
            $headers[trim($header['name'])] = trim($header['value']);
        }

        if ($authorization = $this->authorization) {
            $headers['Authorization'] = $authorization;
        }

        return apply_filters('http_bridge_backend_headers', $headers, $this);
    }

    private function authorization()
    {
        if (!$this->is_valid) {
            return;
        }

        if (!isset($this->data['authentication'])) {
            return;
        }

        $type = $this->data['authentication']['type'];
        $client_id = $this->data['authentication']['client_id'];
        $client_secret = $this->data['authentication']['client_secret'];

        if ($type === 'Basic') {
            return 'Basic ' . base64_encode("{$client_id}:{$client_secret}");
        } elseif ($type === 'Token') {
            return "token {$client_id}:{$client_secret}";
        } elseif ($type === 'Bearer') {
            return "Bearer {$client_secret}";
        }
    }

    /**
     * Gets backend default request body content type encoding schema.
     *
     * @return string|null Encoding schema.
     */
    private function content_type()
    {
        if (!$this->is_valid) {
            return;
        }

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
        if (!$this->is_valid) {
            return;
        }

        $parsed = wp_parse_url((string) $path);
        $query = $parsed['query'] ?? null;
        if (isset($parsed['path'])) {
            $path = preg_replace('/^\/+/', '', $parsed['path']);
        } else {
            $path = '';
        }

        $parsed = wp_parse_url($this->base_url ?? '');
        if (isset($parsed['path'])) {
            $base_path = preg_replace('/^\/+/', '', $parsed['path']);
            $path = preg_replace(
                '/^' . preg_quote($base_path, '/') . '/',
                '',
                $path
            );
        }

        $url =
            preg_replace('/\/+$/', '', $this->base_url) .
            '/' . $path;

        if ($query) {
            $url .= '?' . $query;
        }

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
        if (!$this->is_valid) {
            return new WP_Error('invalid_backend');
        }

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
        if (!$this->is_valid) {
            return new WP_Error('invalid_backend');
        }

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
        if (!$this->is_valid) {
            return new WP_Error('invalid_backend');
        }

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
        if (!$this->is_valid) {
            return new WP_Error('invalid_backend');
        }

        $url = $this->url($endpoint);
        $headers = array_merge($this->headers(), (array) $headers);
        return http_bridge_delete($url, $params, $headers);
    }

    public function data()
    {
        if (!$this->is_valid) {
            return;
        }

        return $this->data;
    }

    public function save()
    {
        if (!$this->is_valid) {
            return false;
        }

        $setting = HTTP_Bridge::setting('general');
        if (!$setting) {
            return false;
        }

        $backends = $setting->backends ?: [];

        $index = array_search(
            $this->name,
            array_column($backends, 'name'),
        );

        if ($index === false) {
            $backends[] = $this->data;
        } else {
            $backends[$index] = $this->data;
        }

        $setting->backends = $backends;

        return true;
    }

    public function remove()
    {
        if (!$this->is_valid) {
            return false;
        }

        $setting = HTTP_Bridge::setting('general');
        if (!$setting) {
            return false;
        }

        $backends = $setting->backends ?: [];

        $index = array_search(
            $this->name,
            array_column($backends, 'name'),
        );

        if (!$index === false) {
            return false;
        }

        array_splice($backends, $index, 1);
        $setting->backends = $backends;

        return true;
    }
}
