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
            'title' => 'http-backend',
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
                        'schema' => [
                            'type' => 'string',
                            'enum' => ['Basic', 'Token', 'Bearer'],
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
                    'required' => ['schema', 'client_id', 'client_secret'],
                ],
            ],
            'required' => ['name', 'base_url', 'headers'],
            'additionalProperties' => false,
        ];
    }

    /**
     * Ephemeral backend registration as an interceptor to allow
     * the use of non registered backends.
     *
     * @param array $data Backend data.
     */
    public static function temp_registration($data)
    {
        if (empty($data) || !isset($data['name'])) {
            return;
        }

        add_filter(
            'http_bridge_backends',
            static function ($backends) use ($data) {
                foreach ($backends as $candidate) {
                    if ($candidate->name === $data['name']) {
                        $backend = $candidate;
                        break;
                    }
                }

                if (!isset($backend)) {
                    $backend = new Http_Backend($data);

                    if ($backend->is_valid) {
                        $backends[] = $backend;
                    }
                }

                return $backends;
            },
            99,
            1
        );
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

        if (!isset($this->data['authentication']['schema'])) {
            return;
        }

        $schema = $this->data['authentication']['schema'];
        $client_id = $this->data['authentication']['client_id'];
        $client_secret = $this->data['authentication']['client_secret'];

        if ($schema === 'Basic') {
            return 'Basic ' . base64_encode("{$client_id}:{$client_secret}");
        } elseif ($schema === 'Token') {
            return "token {$client_id}:{$client_secret}";
        } elseif ($schema === 'Bearer') {
            return "Bearer {$client_secret}";
        }
    }

    public function authorized($schema, $client_id, $client_secret, $realm = null, $endpoint = '/')
    {
        if (!$this->is_valid) {
            return $this;
        }

        switch ($schema) {
            case 'Basic':
                $authorization = 'Basic ' . base64_encode("{$client_id}:{$client_secret}");

                return $this->clone([
                    'headers' => array_merge($this->data['headers'], [
                        ['name' => 'Authorization', 'value' => $authorization]
                    ])
                ]);
            case 'Digest':
                $response = $this->head($this->endpoint);
                if (is_wp_error($response)) {
                    return $response;
                }

                $header = $response['headers']['WWW-Authenticate'] ?? null;
                if (!$header) {
                    return new WP_Error('unauthorized');
                }

                $fields = ['realm' => null, 'nonce' => null, 'opaque' => null];
                foreach (array_keys($fields) as $field) {
                    if (!preg_match("/{$field}=\"([^\"]+)\"/", $header, $matches)) {
                        return new WP_Error('unauthorized');
                    }

                    $fields[$field] = $matches[1];
                }

                if ($fields['realm'] !== $realm) {
                    return new WP_Error('unauthorized');
                }

                $a1 = md5("{$client_id}:{$realm}:{$client_secret}");
                $a2 = md5("{$this->method}:{$this->endpoint}");
                $response = md5("{$a1}:{$fields['nonce']}:{$a2}");
                $uri = $this->url($endpoint);

                $authorization = "Digest username=\"{$client_id}\" realm=\"{$realm}\" nonce=\"{$fields['nonce']}\" opaque=\"{$fields['opaque']}\" uri=\"{$uri}\" response=\"{$response}\"";

                return $this->clone([
                    'headers' => array_merge($this->data['headers'], [
                        ['name' => 'Authorization', 'value' => $authorization],
                    ])
                ]);
            case 'Token':
                $authorization = "token {$client_id}:{$client_secret}";
                return $this->clone([
                    'headers' => array_merge($this->data['headers'], [
                        ['name' => 'Authorization', 'value' => $authorization]
                    ]),
                ]);
            case 'URL':
                $parsed = wp_parse_url($this->base_url);
                $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
                $path = $parsed['path'] ?? '';
                $base_url = "{$parsed['scheme']}://{$client_id}:{$client_secret}@{$parsed['host']}{$port}{$path}";

                return $this->clone([
                    'base_url' => $base_url,
                ]);
            default:
                return $this;
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

        $url = preg_replace('/\/+$/', '', $this->base_url) . '/' . $path;

        if ($query) {
            $url .= '?' . $query;
        }

        return apply_filters('http_bridge_backend_url', $url, $this);
    }

    public function head($endpoint, $params = [], $headers = [])
    {
        if (!$this->is_valid) {
            return new WP_Error('invalid_backend');
        }

        $url = $this->url($endpoint);
        $headers = array_merge($this->headers(), (array) $headers);
        return http_bridge_head($url, $params, $headers);
    }

    /**
     * Performs a GET HTTP request to the backend.
     *
     * @param string $endpoint Target backend endpoint as relative path.
     * @param array $params URL query params.
     * @param array $headers Additional HTTP headers.
     * @param array $args Additional request args.
     *
     * @return array|WP_Error Request response.
     */
    public function get($endpoint, $params = [], $headers = [], $args = [])
    {
        if (!$this->is_valid) {
            return new WP_Error('invalid_backend');
        }

        $url = $this->url($endpoint);
        $headers = array_merge($this->headers(), (array) $headers);
        return http_bridge_get($url, $params, $headers, $args);
    }

    /**
     * Performs a POST HTTP request to the backend.
     *
     * @param string $endpoint Target backend endpoint as relative path.
     * @param array $params URL query params.
     * @param array $headers Additional HTTP headers.
     * @param array $files Map with names and filepaths.
     * @param array $args Additional request args.
     *
     * @return array|WP_Error Request response.
     */
    public function post($endpoint, $data = [], $headers = [], $files = [], $args = [])
    {
        if (!$this->is_valid) {
            return new WP_Error('invalid_backend');
        }

        $url = $this->url($endpoint);
        $headers = array_merge($this->headers(), (array) $headers);
        return http_bridge_post($url, $data, $headers, $files, $args);
    }

    /**
     * Performs a PUT HTTP request to the backend.
     *
     * @param string $endpoint Target backend endpoint as relative path.
     * @param array $params URL query params.
     * @param array $headers Additional HTTP headers.
     * @param array $files Map with names and filepaths.
     * @param array $args Additional request args.
     *
     * @return array|WP_Error Request response.
     */
    public function put($endpoint, $data = [], $headers = [], $files = [], $args = [])
    {
        if (!$this->is_valid) {
            return new WP_Error('invalid_backend');
        }

        $url = $this->url($endpoint);
        $headers = array_merge($this->headers(), (array) $headers);
        return http_bridge_put($url, $data, $headers, $files, $args);
    }

    /**
     * Performs a PATCH HTTP request to the backend.
     *
     * @param string $endpoint Target backend endpoint as relative path.
     * @param array $params URL query params.
     * @param array $headers Additional HTTP headers.
     * @param array $files Map with names and filepaths.
     * @param array $args Additional request args.
     *
     * @return array|WP_Error Request response.
     */
    public function patch($endpoint, $data = [], $headers = [], $files = [], $args = [])
    {
        if (!$this->is_valid) {
            return new WP_Error('invalid_backend');
        }

        $url = $this->url($endpoint);
        $headers = array_merge($this->headers(), (array) $headers);
        return http_bridge_patch($url, $data, $headers, $files, $args);
    }

    /**
     * Performs a DELETE HTTP request to the backend.
     *
     * @param string $endpoint Target backend endpoint as relative path.
     * @param array $params URL query params.
     * @param array $headers Additional HTTP headers.
     * @param array $args Additional request args.
     *
     * @return array|WP_Error Request response.
     */
    public function delete($endpoint, $params = [], $headers = [], $args = [])
    {
        if (!$this->is_valid) {
            return new WP_Error('invalid_backend');
        }

        $url = $this->url($endpoint);
        $headers = array_merge($this->headers(), (array) $headers);
        return http_bridge_delete($url, $params, $headers, $args);
    }

    public function clone($partial = [])
    {
        if (!$this->is_valid) {
            return $this;
        }

        $data = array_merge($this->data, $partial);
        return new static($data);
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

        $index = array_search($this->name, array_column($backends, 'name'));

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

        $index = array_search($this->name, array_column($backends, 'name'));

        if (!$index === false) {
            return false;
        }

        array_splice($backends, $index, 1);
        $setting->backends = $backends;

        return true;
    }
}
