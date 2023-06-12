<?php

// Performs a get request to Odoo
function wpct_oc_get_odoo($endpoint, $headers = array())
{
	$url = wpct_oc_req_endpoint($endpoint);
	$args = array('method' => 'GET', 'headers' => wpct_oc_req_headers($headers));
	return wpct_oc_request($url, $args);
}

// Performs a post request to Odoo
function wpct_oc_post_odoo($endpoint, $data, $headers = array())
{
	$url = wpct_oc_req_endpoint($endpoint);
	$payload = json_encode($data);
	$headers = wpct_oc_req_headers($headers);
	$headers['Content-Type'] = 'application/json';
	$args = array(
		'method' => 'POST',
		'headers' => $headers,
		'body' => $payload
	);

	return wpct_oc_request($url, $args);
}

// Performs a put request to Odoo
function wpct_oc_put_odoo($endpoint, $data, $headers = array())
{
	$url = wpct_oc_req_endpoint($endpoint);
	$payload = json_encode($data);
	$headers = wpct_oc_req_headers($headers);
	$headers['Content-Type'] = 'application/json';
	$args = array(
		'method' => 'PUT',
		'headers' => $headers,
		'body' => $payload
	);

	return wpct_oc_request($url, $args);
}

// Performs a delete request to Odoo
function wpct_oc_delete_odoo($endpoint, $headers = array())
{
	$url = wpct_oc_delete_odoo($endpoint);
	$args = array('method' => 'DELETE', 'headers' => wpct_oc_req_headers($headers));
	return wpct_oc_request($url, $args);
}

// request gateway with error handling
function wpct_oc_request($url, $args)
{
	$response = wp_remote_request($url, $args);
	if (!is_wp_error($response)) {
		return $response;
	}

	return false;
}

// Map endpoint to an absolute url
function wpct_oc_req_endpoint($endpoint)
{
	$url = parse_url($endpoint);
	if (isset($url['scheme'])) {
		return $endpoint;
	} else {
		return preg_replace('/\/$/', '', wpct_oc_option_getter('wpct_oc_base_url')) . '/' . preg_replace('/^\//', '', $endpoint);
	}
}

// Middleware headers setter
function wpct_oc_req_headers($request_headers = array())
{
	$request_headers['Connection'] = 'keep-alive';
	$request_headers['Accept'] = 'application/json';
	$request_headers['API-KEY'] = wpct_oc_option_getter('wpct_oc_api_key');
	$request_headers['Accept-Language'] = wpct_oc_accept_language_header();
	return $request_headers;
}
