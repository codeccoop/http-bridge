<?php

use HTTP_BRIDGE\Http_Client;

if (!defined('ABSPATH')) {
    exit();
}

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
    return Http_Client::get($url, ['params' => $params, 'headers' => $headers]);
}

// binds get requests to custom wp action
add_action('http_bridge_get', 'http_bridge_get', 10, 3);

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

// binds post requests to custom wp action
add_action('http_bridge_post', 'http_bridge_post', 10, 4);

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

// binds put requests to custom wp action
add_action('http_bridge_put', 'http_bridge_put', 10, 4);

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

// binds delete requests to custom wp action
add_action('http_bridge_delete', 'http_bridge_delete', 10, 3);
