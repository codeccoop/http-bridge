<?php

use HTTP_BRIDGE\Http_Client;

if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('http_bridge_get')) {
    /**
     * Public function to perform a GET requests.
     *
     * @param string $url Target URL.
     * @param array $args Request arguments.
     *
     * @return array|WP_Error Response data or error.
     */
    function http_bridge_get($url, $params = [], $headers = [])
    {
        return Http_Client::get($url, [
            'params' => $params,
            'headers' => $headers,
        ]);
    }
}

if (!function_exists('http_bridge_post')) {
    /**
     * Public function to perform a POST requests.
     *
     * @param string $url Target URL.
     * @param array $args Request arguments.
     *
     * @return array|WP_Error Response data or error.
     */
    function http_bridge_post($url, $data = [], $headers = [], $files = [])
    {
        return Http_Client::post($url, [
            'data' => $data,
            'headers' => (array) $headers,
            'files' => (array) $files,
        ]);
    }
}

if (!function_exists('http_bridge_put')) {
    /**
     * Public function to perform a PUT requests.
     *
     * @param string $url Target URL.
     * @param array $args Request arguments.
     *
     * @return array|WP_Error Response data or error.
     */
    function http_bridge_put($url, $data = [], $headers = [], $files = [])
    {
        return Http_Client::put($url, [
            'data' => $data,
            'headers' => (array) $headers,
            'files' => (array) $files,
        ]);
    }
}

if (!function_exists('http_bridge_delete')) {
    /**
     * Public function to perform a DELETE requests.
     *
     * @param string $url Target URL.
     * @param array $args Request arguments.
     *
     * @return array|WP_Error Response data or error.
     */
    function http_bridge_delete($url, $params = [], $headers = [])
    {
        return Http_Client::delete($url, [
            'params' => $params,
            'headers' => $headers,
        ]);
    }
}
