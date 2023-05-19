<?php

function wpct_oc_post_odoo($data,$endpoint) {
	$post_url = wpct_oc_get_odoo_base_url().$endpoint;
	$post_data = json_encode($data);
	$args = array(
		'headers' => array(
			'Content-Type' => 'application/json',
			'Connection' => 'keep-alive',
			'accept' => 'application/json',
			'API-KEY' => wpct_oc_get_api_key()
		),
		'body' => $post_data
	);
	$response = wp_remote_post( $post_url, $args);
	if ( !is_wp_error( $response ) ) {
		return $response;
	}
	return false;
}
