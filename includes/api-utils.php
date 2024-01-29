<?php

// Performs a get request to Odoo
function wpct_hb_get($endpoint, $headers = [])
{
    $url = wpct_hb_req_endpoint($endpoint);
    $args = [
        'method' => 'GET',
        'headers' => wpct_hb_req_headers($headers, 'GET', $url)
    ];
    return wpct_hb_request($url, $args);
}

// Performs a post request to Odoo
function wpct_hb_post($endpoint, $data, $headers = [])
{
    $url = wpct_hb_req_endpoint($endpoint);
    $payload = json_encode($data);
    $headers = wpct_hb_req_headers($headers, 'POST', $url);
    $headers['Content-Type'] = 'application/json';
    $args = [
        'method' => 'POST',
        'headers' => $headers,
        'body' => $payload
    ];

    return wpct_hb_request($url, $args);
}

// Performs a put request to Odoo
function wpct_hb_put($endpoint, $data, $headers = [])
{
    $url = wpct_hb_req_endpoint($endpoint);
    $payload = json_encode($data);
    $headers = wpct_hb_req_headers($headers, 'PUT', $url);
    $headers['Content-Type'] = 'application/json';
    $args = [
        'method' => 'PUT',
        'headers' => $headers,
        'body' => $payload
    ];

    return wpct_hb_request($url, $args);
}

// Performs a delete request to Odoo
function wpct_hb_delete($endpoint, $headers = [])
{
    $url = wpct_hb_req_endpoint($endpoint);
    $args = [
        'method' => 'DELETE',
        'headers' => wpct_hb_req_headers($headers, 'DELETE', $url)
    ];
    return wpct_hb_request($url, $args);
}

// request gateway with error handling
function wpct_hb_request($url, $args)
{
    $response = wp_remote_request($url, $args);
    if (!is_wp_error($response)) return $response;

    return false;
}

// Map endpoint to an absolute url
function wpct_hb_req_endpoint($endpoint)
{
    $url = parse_url($endpoint);
    if (isset($url['scheme'])) {
        return $endpoint;
    } else {
        return preg_replace('/\/$/', '', wpct_hb_option_getter('wpct_hb_base_url')) . '/' . preg_replace('/^\//', '', $endpoint);
    }
}

// Middleware headers setter
function wpct_hb_req_headers($request_headers, $method, $url)
{
    $request_headers['Connection'] = 'keep-alive';
    $request_headers['Accept'] = 'application/json';
    $request_headers['API-KEY'] = wpct_hb_option_getter('wpct_hb_api_key');
    $request_headers['Accept-Language'] = wpct_hb_accept_language_header();

    return apply_filter('wpct_hb_headers', $request_headers, $method, $url);
}
