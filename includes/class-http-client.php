<?php

namespace WPCT_HTTP;

use Exception;

require_once 'class-multipart.php';

class Http_Client
{
    public static function get($url, $headers = [])
    {
        $url = Http_Client::get_endpoint_url($url);
        $args = [
            'method' => 'GET',
            'headers' => Http_Client::req_headers($headers, 'GET', $url)
        ];
        return Http_Client::do_request($url, $args);
    }

    public static function post($url, $data = [], $headers = [])
    {
        $url = Http_Client::get_endpoint_url($url);
        $payload = json_encode($data);
        $headers = Http_Client::req_headers($headers, 'POST', $url);
        $headers['Content-Type'] = 'application/json';
        $args = [
            'method' => 'POST',
            'headers' => $headers,
            'body' => $payload
        ];

        return Http_Client::do_request($url, $args);
    }

    public static function post_multipart($url, $data = [], $files = [], $headers = [])
    {
        $url = Http_Client::get_endpoint_url($url);

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
        $headers['Content-Type'] = $multipart->content_type();
        $args = [
            'method' => 'POST',
            'headers' => $headers,
            'body' => $multipart->data()
        ];

        return Http_Client::do_request($url, $args);
    }

    public static function put($url, $data = [], $headers = [])
    {
        $url = Http_Client::get_endpoint_url($url);
        $payload = json_encode($data);
        $headers = Http_Client::req_headers($headers, 'PUT', $url);
        $headers['Content-Type'] = 'application/json';
        $args = [
            'method' => 'PUT',
            'headers' => $headers,
            'body' => $payload
        ];

        return Http_Client::do_request($url, $args);
    }

    public static function put_multipart($url, $data = [], $files = [], $headers = [])
    {
        $url = Http_Client::get_endpoint_url($url);
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
        $headers['Content-Type'] = $multipart->content_type();
        $args = [
            'method' => 'PUT',
            'headers' => $headers,
            'body' => $multipart->data()
        ];

        return Http_Client::do_request($url, $args);
    }

    public static function delete($url, $headers = [])
    {
        $url = Http_Client::get_endpoint_url($url);
        $args = [
            'method' => 'DELETE',
            'headers' => Http_Client::req_headers($headers, 'DELETE', $url)
        ];
        return Http_Client::do_request($url, $args);
    }

    private static function do_request($url, $args)
    {
        $response = wp_remote_request($url, $args);
        if (!is_wp_error($response)) {
            return $response;
        }

        return false;
    }

    private static function get_endpoint_url($url)
    {
        $url_data = parse_url($url);
        if (isset($url_data['scheme'])) {
            return $url;
        } else {
            $base_url = Http_Client::option_getter('wpct-http-bridge_general', 'base_url');
            return preg_replace('/\/$/', '', $base_url . '/' . preg_replace('/^\//', '', $url));
        }
    }

    private static function req_headers($headers, $method = null, $url = null)
    {
        $headers['Connection'] = 'keep-alive';
        $headers['Accept'] = 'application/json';
        $headers['API-KEY'] = Http_Client::option_getter('wpct-http-bridge_general', 'api_key');
        $headers['Accept-Language'] = Http_Client::get_locale();

        return apply_filters('wpct_http_headers', $headers, $method, $url);
    }

    private static function option_getter($setting, $option)
    {
        $setting = get_option($setting);
        if (!$setting) {
            throw new Exception('Wpct Http Bridge: You should configure base url on plugin settings');
        }

        return isset($setting[$option]) ? $setting[$option] : null;
    }

    private static function get_locale()
    {
        $locale = apply_filters('wpct_i18n_current_language', null, 'locale');
        if ($locale) {
            return $locale;
        }

        return get_locale();
    }
}

// Performs a get request to Odoo
function wpct_http_get($url, $headers = [])
{
    return Http_Client::get($url, $headers);
}

// Performs a post request to Odoo
function wpct_http_post($url, $data = [], $headers = [])
{
    return Http_Client::post($url, $data, $headers);
}

function wpct_hn_post_multipart($url, $data = [], $files = [], $headers = [])
{
    return Http_Client::post_multipart($url, $data, $files, $headers);
}

// Performs a put request to Odoo
function wpct_http_put($url, $data = [], $headers = [])
{
    return Http_Client::put($url, $data, $headers);
}

function wpct_http_put_multipart($url, $data = [], $files = [], $headers = [])
{
    return Http_Client::put_multipart($url, $data, $files, $headers);
}

// Performs a delete request to Odoo
function wpct_http_delete($url, $headers = [])
{
    return Http_Client::delete($url, $headers);
}
