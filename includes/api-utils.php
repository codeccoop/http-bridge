<?php

function wpct_oc_post_odoo($data, $endpoint)
{
	$url = parse_url($endpoint);
	if (isset($url['scheme'])) {
		$post_url = $endpoint;
	} else {
		$post_url = preg_replace('/\/$/', '', wpct_oc_get_odoo_base_url()) . '/' . preg_replace('/^\//', '', $endpoint);
	}

	$post_data = json_encode($data);
	$args = array(
		'headers' => wpct_oc_set_headers(array(
			'Content-Type' => 'application/json',
			'Connection' => 'keep-alive',
			'Accept' => 'application/json',
		)),
		'body' => $post_data
	);

	$response = wp_remote_post($post_url, $args);
	if (!is_wp_error($response)) {
		return $response;
	}

	return false;
}
