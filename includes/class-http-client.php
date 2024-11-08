<?php

namespace WPCT_HTTP;

use WP_Error;

require_once 'class-multipart.php';

class Http_Client
{
    private static const settings_defaults = [
		'params' => [],
        'data' => [],
        'headers' => [
            'connection' => 'keep-alive',
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ],
        'files' => []
    ];

    public static function req_settings($settings = [])
    {
        $settings = array_merge(Http_Client::settings_defaults, (array) $settings);
		$settings['headers'] = Http_Client::req_headers($settings['headers']);

		return $settings;
    }

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

    public static function get($url, $params = [], $headers = [])
    {
		$url = Http_Client::add_query_str($url, $params);
        $args = [
            'method' => 'GET',
            'headers' => Http_Client::req_headers($headers, 'GET', $url)
        ];
        return Http_Client::do_request($url, $args);
    }

    public static function post($url, $data, $headers, $files = null)
    {
        if (is_array($files)) {
            return Http_Client::post_multipart($url, $data, $files, $headers);
        }

        $body = is_string($data) ? $data : json_encode($data);
        $headers = Http_Client::req_headers($headers, 'POST', $url);

        return Http_Client::do_request($url, [
            'method' => 'POST',
            'headers' => $headers,
            'body' => $body
        ]);
    }

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

    public static function put($url, $data, $headers, $files = null)
    {
		if (is_array($files)) {
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

    public static function delete($url, $params, $headers)
    {
		$url = Http_Client::add_query_str($url, $params);
        return Http_Client::do_request($url, [
            'method' => 'DELETE',
            'headers' => Http_Client::req_headers($headers, 'DELETE', $url)
        ]);
    }

    private static function do_request($url, $args)
    {
        $request = apply_filters('wpct_http_request_args', ['url' => $url, 'args' => $args]);
        $response = wp_remote_request($url, $args);
        if (is_wp_error($response)) {
            $response->add_data(['request' => $request]);
            return $response;
        }

        if ($response['response']['code'] !== 200) {
            return new WP_Error(
                'wpct_http_error',
                __("Http error response status code: Request to {$url} with {$args['method']} method", 'wpct-http-bridge'),
                [
                    'request' => $request,
                    'response' => $response,
                ],
            );
        }

        return $response;
    }

	private static function req_headers($headers)
	{
		return array_merge([
			'host' => $_SERVER['HTTP_HOST'],
			'referer' => $_SERVER['HTTP_REFERER'],
			'accept-language' => Http_Client::get_locale()
		], (array) $headers);
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
function wpct_http_get($url, $settings = [])
{
	['params' => $params, 'headers' => $headers ] = Http_Client::req_settings($settings);
    return Http_Client::get($url, $params, $headers);
}

// Performs a post request to Odoo
function wpct_http_post($url, $settings = []) //$data = [], $headers = [])
{
	['data' => $data, 'headers' => $headers, 'files' => $files ] = Http_Client::req_settings($settings);
    return Http_Client::post($url, $data, $headers, $files);
}

// Performs a put request to Odoo
function wpct_http_put($url, $settings = [])
{
	['data' => $data, 'headers' => $headers, 'files' => $files ] = Http_Client::req_settings($settings);
    return Http_Client::put($url, $data, $headers, $files);
}

// Performs a delete request to Odoo
function wpct_http_delete($url, $settings = [])
{
	['params' => $params, 'headers' => $headers ] = Http_Client::req_settings($settings);
    return Http_Client::delete($url, $params, $headers);
}
