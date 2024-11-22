<?php

namespace HTTP_BRIDGE;

use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

require_once 'class-multipart.php';

/**
 * HTTP Client.
 */
class Http_Client
{
    /**
    * Default request arguments.
    *
    * @var array $args_defaults Default request arguments.
    */
    private const args_defaults = [
        'params' => [],
        'data' => [],
        'headers' => [
            'connection' => 'keep-alive',
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ],
        'files' => []
    ];

    /**
    * Fills request arguments with defaults.
    *
    * @param array $args Request arguments.
    * @return array $args Request arguments with defaults.
    */
    public static function req_args($args = [])
    {
        $args = array_merge(Http_Client::args_defaults, (array) $args);
        $args['headers'] = Http_Client::req_headers($args['headers']);

        return $args;
    }

    /**
    * Add query params to URLs.
    *
    * @param string $url Target URL.
    * @param array $params Associative array with query params.
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

        return $url . '?'. http_build_query($params);
    }

    /**
    * Performs a GET request.
    *
    * @param string $url Target url.
    * @param array $params Associative array with query params.
    * @param array $headers Associative array with HTTP headers.
    * @return array|WP_Error $response Response data or error.
    */
    public static function get($url, $params = [], $headers = [])
    {
        $url = Http_Client::add_query_str($url, $params);
        $args = [
            'method' => 'GET',
            'headers' => Http_Client::req_headers($headers, 'GET', $url)
        ];
        return Http_Client::do_request($url, $args);
    }

    /**
    * Performs a POST request. Default content type is application/json, any other
    * mimetype should be encoded before and passed in as string.
    * If $files is defined and is array, content type switches to multipart/form-data.
    *
    * @param string $url Target URL.
    * @param array $data Associative array with the request payload.
    * @param array $headers Associative array with HTTP headers.
    * @param array $files Associative array with filename and paths.
    * @return array|WP_Error $response Response data or error.
    */
    public static function post($url, $data, $headers, $files = null)
    {
        if (is_array($files) && !empty($files)) {
            return Http_Client::post_multipart($url, $data, $files, $headers);
        }

        $body = is_string($data) ? $data : json_encode($data);

        return Http_Client::do_request($url, [
            'method' => 'POST',
            'headers' => $headers,
            'body' => $body
        ]);
    }

    /**
    * Performs a POST request with multipart/form-data content type payload.
    *
    * @param string $url Target URL.
    * @param array $data Associative array with the request payload.
    * @param array $files Associative array with filename and paths.
    * @param array $headers Associative array with HTTP headers.
    * @return array|WP_Error $response Response data or error.
    */
    public static function post_multipart($url, $data, $files, $headers)
    {
        $multipart = new Multipart();
        $multipart->add_array($data);
        foreach ($files as $name => $path) {
            if (empty($path)) {
                continue;
            }
            $filename = basename($path);
            $filetype = wp_check_filetype($filename);
            if (!$filetype['type']) {
                $filetype['type'] = mime_content_type($path);
            }

            $multipart->add_file($name, $path, $filetype['type']);
        }

        $headers = Http_Client::req_headers($headers, 'POST', $url);
        $headers['content-type'] = $multipart->content_type();

        return Http_Client::do_request($url, [
            'method' => 'POST',
            'headers' => $headers,
            'body' => $multipart->data()
        ]);
    }

    /**
    * Performs a PUT request. Default content type is application/json, any other
    * mimetype should be encoded before and passed in as string.
    * If $files is defined and is array, content type switches to multipart/form-data.
    *
    * @param string $url Target URL.
    * @param array $data Associative array with the request payload.
    * @param array $headers Associative array with HTTP headers.
    * @param array $files Associative array with filename and paths.
    * @return array|WP_Error $response Response data or error.
    */
    public static function put($url, $data, $headers, $files = null)
    {
        if (is_array($files) && !empty($files)) {
            return Http_Client::put_multipart($url, $data, $files, $headers);
        }

        $payload = is_string($data) ? $data : json_encode($data);
        $headers = Http_Client::req_headers($headers, 'PUT', $url);

        return Http_Client::do_request($url, [
            'method' => 'PUT',
            'headers' => $headers,
            'body' => $payload
        ]);
    }

    /**
    * Performs a PUT request with multipart/form-data content type payload.
    *
    * @param string $url Target URL.
    * @param array $data Associative array with the request payload.
    * @param array $files Associative array with filename and paths.
    * @param array $headers Associative array with HTTP headers.
    * @return array|WP_Error $response Response data or error.
    */
    private static function put_multipart($url, $data, $files, $headers)
    {
        $multipart = new Multipart();
        $multipart->add_array($data);
        foreach ($files as $name => $path) {
            $filename = basename($path);
            $filetype = wp_check_filetype($filename);
            if (!$filetype['type']) {
                $filetype['type'] = mime_content_type($path);
            }

            $multipart->add_file($name, $path, $filetype['type']);
        }

        $headers = Http_Client::req_headers($headers, 'PUT', $url);
        $headers['content-type'] = $multipart->content_type();

        return Http_Client::do_request($url, [
            'method' => 'PUT',
            'headers' => $headers,
            'body' => $multipart->data()
        ]);
    }

    /**
    * Performs a DETELE request.
    *
    * @param string $url Target url.
    * @param array $params Associative array with query params.
    * @param array $headers Associative array with HTTP headers.
    * @return array|WP_Error $response Response data or error.
    */
    public static function delete($url, $params, $headers)
    {
        $url = Http_Client::add_query_str($url, $params);
        return Http_Client::do_request($url, [
            'method' => 'DELETE',
            'headers' => Http_Client::req_headers($headers, 'DELETE', $url)
        ]);
    }

    /**
    * Performs a request on top of WP_Http client
    *
    * @param  string  $url Target URL.
    * @param  array $args  WP_Http::request arguments.
    * @return array|WP_Error $response Response data or error.
    */
    private static function do_request($url, $args)
    {
        global $wp_version;
        $args = array_merge(
            [
                'method' => 'POST',
                'timeout' => 5,
                'redirection' => 5,
                'httpversion' => '1.0',
                'user-agent' => 'WordPress/' . $wp_version . '; ' . home_url(),
                'blocking' => true,
                'headers' => [],
                'cookies' => [],
                'body' => null,
                'compress' => false,
                'decompress' => true,
                'sslverify' => true,
                'stream' => false,
                'filename' => null
            ],
            $args
        );

        $request = apply_filters('http_bridge_request_args', ['url' => $url, 'args' => $args]);
        $response = wp_remote_request($request['url'], $request['args']);
        if (is_wp_error($response)) {
            $response->add_data(['request' => $request]);
            return $response;
        }

        if ($response['response']['code'] !== 200) {
            return new WP_Error(
                'http_bridge_error',
                __("HTTP error response status code: Request to {$url} with {$args['method']} method", 'http-bridge'),
                [
                    'request' => $request,
                    'response' => $response,
                ],
            );
        }

        return $response;
    }

    /**
    * Add default headers to an array.
    *
    * @param array $headers Associative array with HTTP headers.
    * @return array $headers Associative array with HTTP headers.
    */
    private static function req_headers($headers)
    {
        return array_merge([
            'host' => $_SERVER['HTTP_HOST'],
            'referer' => $_SERVER['HTTP_REFERER'],
            'accept-language' => Http_Client::get_locale(),
            'content-type' => 'application/json',
        ], (array) $headers);
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

/**
* Public function to perform a GET requests.
*
* @param string $url Target URL.
* @param array $args Associative array with request arguments.
* @return array|WP_Error $response Response data or error.
*/
function http_bridge_get($url, $args = [])
{
    ['params' => $params, 'headers' => $headers ] = Http_Client::req_args($args);
    return Http_Client::get($url, $params, $headers);
}

/**
* Public function to perform a POST requests.
*
* @param string $url Target URL.
* @param array $args Associative array with request arguments.
* @return array|WP_Error $response Response data or error.
*/
function http_bridge_post($url, $args = [])
{
    ['data' => $data, 'headers' => $headers, 'files' => $files ] = Http_Client::req_args($args);
    return Http_Client::post($url, $data, $headers, $files);
}

/**
* Public function to perform a PUT requests.
*
* @param string $url Target URL.
* @param array $args Associative array with request arguments.
* @return array|WP_Error $response Response data or error.
*/
function http_bridge_put($url, $arguments = [])
{
    ['data' => $data, 'headers' => $headers, 'files' => $files ] = Http_Client::req_args($arguments);
    return Http_Client::put($url, $data, $headers, $files);
}

/**
* Public function to perform a DELETE requests.
*
* @param string $url Target URL.
* @param array $args Associative array with request arguments.
* @return array|WP_Error $response Response data or error.
*/
function http_bridge_delete($url, $args = [])
{
    ['params' => $params, 'headers' => $headers ] = Http_Client::req_args($args);
    return Http_Client::delete($url, $params, $headers);
}
