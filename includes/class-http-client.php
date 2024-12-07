<?php

namespace HTTP_BRIDGE;

use WP_Error;

if (!defined('ABSPATH')) {
    exit();
}

require_once 'class-multipart.php';

/**
 * HTTP Client.
 */
class Http_Client
{
    /**
     * Fills request arguments with defaults.
     *
     * @param array $args Request arguments.
     *
     * @return array Request arguments with defaults.
     */
    private static function default_args($args = [])
    {
        foreach ($args as $arg => $value) {
            switch ($arg) {
                case 'data':
                    if (!(is_string($value) || is_array($value))) {
                        $args[$arg] = [];
                    }
                    break;
                case 'params':
                case 'headers':
                case 'files':
                    $args[$arg] = (array) $value;
            }
        }

        $args = array_merge(
            [
                'params' => [],
                'data' => [],
                'headers' => [],
                'files' => [],
            ],
            (array) $args
        );

        $args['headers'] = static::default_headers($args['headers']);

        return $args;
    }

    /**
     * Add default headers to the request headers.
     *
     * @param array $headers Associative array with HTTP headers.
     *
     * @return array $headers Associative array with HTTP headers.
     */
    private static function default_headers($headers)
    {
        $headers = array_merge(
            [
                'Origin' => $_SERVER['HTTP_HOST'],
                'Referer' => $_SERVER['HTTP_REFERER'],
                'Accept-Language' => static::get_locale(),
                'Connection' => 'keep-alive',
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
            self::normalize_headers($headers)
        );

        foreach ($headers as $name => $value) {
            if (empty($value)) {
                unset($headers[$name]);
            }
        }

        return $headers;
    }

    /**
     * Normalize HTTP header names to avoid duplications.
     *
     * @param array $headers HTTP headers.
     *
     * @return array Normalized HTTP headers.
     */
    private static function normalize_headers($headers)
    {
        $normalized = [];
        foreach ((array) $headers as $name => $value) {
            if (empty($name)) {
                continue;
            }
            $name = str_replace('_', '-', $name);
            $name = implode(
                '-',
                array_map(function ($chunk) {
                    return ucfirst($chunk);
                }, explode('-', trim($name)))
            );
            $normalized[$name] = $value;
        }
        return $normalized;
    }

    /**
     * Add query params to URLs.
     *
     * @param string $url Target URL.
     * @param array $params Associative array with query params.
     *
     * @return string URL with query params.
     */
    private static function add_query_str($url, $params)
    {
        $parsed = parse_url($url);
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $query);
            $params = array_merge($query, $params);
            $url = preg_replace('/?.*$/', '', $url);
        }

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    /**
     * Performs a GET request.
     *
     * @param string $url Target URL.
     * @param array $args WP_Http::request arguments.
     *
     * @return array|WP_Error Response data or error.
     */
    public static function get($url, $args = [])
    {
        if (!is_array($args)) {
            $args = [];
        }

        return static::do_request(
            $url,
            array_merge($args, [
                'method' => 'GET',
            ])
        );
    }

    /**
     * Performs a POST request.
     * If files were defined in args, content type is forced to multipart/form-data.
     *
     * @param string $url Target URL.
     * @param array $params Associative array with query params.
     *
     * @return array|WP_Error Response data or error.
     */
    public static function post($url, $args = [])
    {
        if (!is_array($args)) {
            $args = [];
        }

        if (!empty($args['files']) && is_array($args['files'])) {
            return static::do_multipart(
                $url,
                array_merge($args, ['method' => 'POST'])
            );
        }

        return static::do_request(
            $url,
            array_merge($args, [
                'method' => 'POST',
            ])
        );
    }

    /**
     * Performs a PUT request.
     * If files were defined in args, content type is forced to multipart/form-data.
     *
     * @param string $url Target URL.
     * @param array $params Associative array with query params.
     *
     * @return array|WP_Error Response data or error.
     */
    public static function put($url, $args)
    {
        if (!is_array($args)) {
            $args = [];
        }

        if (!empty($args['files']) && is_array($args['files'])) {
            return static::do_multipart(
                $url,
                array_merge($args, ['method' => 'PUT'])
            );
        }

        return static::do_request(
            $url,
            array_merge($args, [
                'method' => 'PUT',
            ])
        );
    }

    /**
     * Performs a DETELE request.
     *
     * @param string $url Target URL.
     * @param array $params Associative array with query params.
     *
     * @return array|WP_Error Response data or error.
     */
    public static function delete($url, $args)
    {
        if (!is_array($args)) {
            $args = [];
        }

        return static::do_request(
            $url,
            array_merge($args, [
                'method' => 'DELETE',
            ])
        );
    }

    /**
     * Proxy to do_request with body encoded as multipart/form-data with binary
     * files as fields.
     *
     * @param string $url Target URL.
     * @param array $args Request arguments.
     *
     * @return array|WP_Error Request response.
     */
    private static function do_multipart($url, $args)
    {
        $args = static::default_args($args);

        $multipart = new Multipart();

        // Add body to the multipart data
        if (isset($args['data'])) {
            // If body is encoded, then try to decode before set to the multipart payload
            if (is_string($args['data'])) {
                $content_type = static::get_content_type($args['headers']);
                if ($content_type === 'application/json') {
                    $data = json_decode($args['data'], JSON_UNESCAPED_UNICODE);
                    $multipart->add_array($data);
                } elseif (
                    $content_type === 'application/x-www-form-urlencoded'
                ) {
                    parse_str($args['data'], $data);
                    $multipart->add_array($data);
                } elseif ($content_type === 'multipart/form-data') {
                    $fields = Multipart::from($args['data'])->decode();
                    foreach ($fields as $field) {
                        if ($field['filename']) {
                            $multipart->add_file(
                                $field['name'],
                                $field['filename'],
                                $field['content-type'],
                                $field['value']
                            );
                        } else {
                            $multipart->add_part(
                                $field['name'],
                                $field['value']
                            );
                        }
                    }
                } else {
                    return new WP_Error(
                        'unkown_content_type',
                        __(
                            'Can\' append files to your payload due to an unkown Content-Type header',
                            'http-bridge'
                        ),
                        ['data' => $args['data'], 'headers' => $args['headers']]
                    );
                }
            } else {
                // Treat body as array
                $multipart->add_array((array) $args['data']);
            }
        }

        // Add files to the request payload data.
        foreach ($args['files'] as $name => $path) {
            if (!(is_file($path) && is_readable($path))) {
                continue;
            }
            $filename = basename($path);
            $filetype = wp_check_filetype($filename);
            if (!$filetype['type']) {
                $filetype['type'] = mime_content_type($path);
            }

            $multipart->add_file($name, $path, $filetype['type']);
        }

        return static::do_request(
            $url,
            array_merge($args, [
                'headers' => array_merge($args['headers'], [
                    'Content-Type' => $multipart->content_type(),
                ]),
                'data' => $multipart->data(),
            ])
        );
    }

    /**
     * Performs a request on top of WP_Http client
     *
     * @param  string  $url Target URL.
     * @param  array $args  WP_Http::request arguments.
     *
     * @return array|WP_Error Response data or error.
     */
    private static function do_request($url, $args)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return new WP_Error(
                'invalid_url',
                sprintf(__('%s is not a valid URL', 'http-bridge'), $url),
                [
                    'url' => $url,
                ]
            );
        }

        $args = static::default_args($args);
        $url = static::add_query_str($url, $args['params']);
        unset($args['params']);

        $content_type = static::get_content_type($args['headers']);

        if (!in_array($args['method'], ['GET', 'DELETE'])) {
            if (!is_string($args['data'])) {
                $data = $args['data'];
                if (!empty($data)) {
                    $mime_type = $content_type;

                    if (strstr($mime_type, 'application/json')) {
                        $args['body'] = json_encode(
                            $data,
                            JSON_UNESCAPED_UNICODE
                        );
                    } elseif (
                        strstr($mime_type, 'application/x-www-form-urlencoded')
                    ) {
                        $args['body'] = http_build_query($data);
                    } elseif (strstr($mime_type, 'multipart/form-data')) {
                        $multipart = new Multipart();
                        $multipart->add_array($data);
                        $args['body'] = $multipart->data();
                        $args['headers'][
                            'Content-Type'
                        ] = $multipart->content_type();
                    } else {
                        return new WP_Error(
                            'posts_bridge_unkown_content_type',
                            sprintf(
                                __(
                                    'Content type %s is unkown. Please, encode request data as string before submit if working with custom content types',
                                    'http-bridge'
                                ),
                                $content_type
                            ),
                            [
                                'Content-Type' => $content_type,
                                'data' => $args['data'],
                            ]
                        );
                    }
                }
            } else {
                $args['body'] = $args['data'];
            }
        }

        unset($args['data']);
        unset($args['files']);

        $request = ['url' => $url, 'args' => $args];
        do_action('http_bridge_before_request', $request);
        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            $response->add_data([
                'request' => ['url' => $url, 'args' => $args],
            ]);
        } else {
            $status = (int) $response['response']['code'];
            if ($status >= 300) {
                $response = new WP_Error(
                    'http_bridge_error',
                    sprintf(
                        __(
                            'HTTP error response status code: Request to %s with %s method',
                            'http-bridge'
                        ),
                        $url,
                        $args['method']
                    ),
                    [
                        'request' => ['url' => $url, 'args' => $args],
                        'response' => $response,
                    ]
                );
            } else {
                $headers = $response['headers']->getAll();
                $content_type = static::get_content_type($headers);
                if ($content_type === 'application/json') {
                    $data = json_decode($response['body'], true);
                } elseif (
                    $content_type === 'application/x-www-form-urlencoded'
                ) {
                    parse_str($response['body'], $data);
                } elseif ($content_type === 'multipart/form-data') {
                    $data = Multipart::from($response['body'])->decode();
                } else {
                    $data = null;
                }

                $response['data'] = $data;
            }
        }

        do_action('http_bridge_response', $response, $request);
        return $response;
    }

    /**
     * Gets mime type from HTTP Content-Type header.
     *
     * @param array $headers HTTP headers.
     *
     * @return string|null Mime type value of the header.
     */
    public static function get_content_type($headers = [])
    {
        $headers = self::normalize_headers($headers);
        $content_type = isset($headers['Content-Type'])
            ? $headers['Content-Type']
            : null;

        if ($content_type) {
            $content_type = preg_replace('/;.*$/', '', $content_type);
            return trim(strtolower($content_type));
        }
    }

    /**
     * Use wpct-i18n to get the current language locale.
     *
     * @return string ISO-2 locale representation.
     */
    private static function get_locale()
    {
        $locale = apply_filters('wpct_i18n_current_language', null, 'locale');
        if ($locale) {
            return $locale;
        }

        return get_locale();
    }
}
