<?php

use HTTP_BRIDGE\Http_Client;

if (!defined('ABSPATH')) {
    exit();
}

if (!function_exists('http_bridge_get')) {
    /**
     * Public function to perform a GET requests.
     *
     * @param string $url Target URL.
     * @param array $params Query params.
     * @param array $headers HTTP headers.
     * @param array $args Request args.
     *
     * @return array|WP_Error Response data or error.
     */
    function http_bridge_get($url, $params = [], $headers = [], $args = [])
    {
        return Http_Client::get(
            $url,
            array_merge($args, [
                'params' => $params,
                'headers' => $headers,
            ])
        );
    }
}

if (!function_exists('http_bridge_post')) {
    /**
     * Public function to perform a POST requests.
     *
     * @param string $url Target URL.
     * @param array $data Request payload.
     * @param array $headers HTTP headers.
     * @param array $files Request files.
     * @param array $args Request args.
     *
     * @return array|WP_Error Response data or error.
     */
    function http_bridge_post(
        $url,
        $data = [],
        $headers = [],
        $files = [],
        $args = []
    ) {
        return Http_Client::post(
            $url,
            array_merge($args, [
                'data' => $data,
                'headers' => (array) $headers,
                'files' => (array) $files,
            ])
        );
    }
}

if (!function_exists('http_bridge_put')) {
    /**
     * Public function to perform a PUT requests.
     *
     * @param string $url Target URL.
     * @param array $data Request payload.
     * @param array $headers HTTP headers.
     * @param array $files Request files.
     * @param array $argss Request args.
     *
     * @return array|WP_Error Response data or error.
     */
    function http_bridge_put(
        $url,
        $data = [],
        $headers = [],
        $files = [],
        $args = []
    ) {
        return Http_Client::put(
            $url,
            array_merge($args, [
                'data' => $data,
                'headers' => (array) $headers,
                'files' => (array) $files,
            ])
        );
    }
}

if (!function_exists('http_bridge_patch')) {
    /**
     * Public function to perform a PUT requests.
     *
     * @param string $url Target URL.
     * @param array $data Request payload.
     * @param array $headers HTTP headers.
     * @param array $files Request files.
     * @param array $argss Request args.
     *
     *
     * @return array|WP_Error Response data or error.
     */
    function http_bridge_patch(
        $url,
        $data = [],
        $headers = [],
        $files = [],
        $args = []
    ) {
        return Http_Client::patch(
            $url,
            array_merge($args, [
                'data' => $data,
                'headers' => (array) $headers,
                'files' => (array) $files,
            ])
        );
    }
}

if (!function_exists('http_bridge_delete')) {
    /**
     * Public function to perform a DELETE requests.
     *
     * @param string $url Target URL.
     * @param array $params Query params.
     * @param array $headers HTTP headers.
     * @param array $args Request args.
     *
     * @return array|WP_Error Response data or error.
     */
    function http_bridge_delete($url, $params = [], $headers = [], $args = [])
    {
        return Http_Client::delete(
            $url,
            array_merge($args, [
                'params' => $params,
                'headers' => $headers,
            ])
        );
    }
}

if (!function_exists('http_bridge_head')) {
    /**
     * Public function to perform an HEAD requests.
     *
     * @param string $url Target URL.
     * @param array $params Query params.
     * @param array $headers HTTP headers.
     * @param array $args Request args.
     *
     * @return array|WP_Error Response data or error.
     */
    function http_bridge_head($url, $params = [], $headers = [], $args = [])
    {
        return Http_Client::head(
            $url,
            array_merge($args, [
                'params' => $params,
                'headers' => $headers,
            ])
        );
    }
}
