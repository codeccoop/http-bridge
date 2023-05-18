<?php
function wpct_oc_post_odoo($data,$endpoint) {
	$post_data = json_encode($data);
	$post_url = wpct_oc_get_odoo_base_url().$endpoint;
	// Prepare new cURL resource
	$crl = curl_init();
	curl_setopt($crl,CURLOPT_URL, $post_url);
	curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($crl, CURLINFO_HEADER_OUT, true);
	curl_setopt($crl, CURLOPT_POST, true);
	curl_setopt($crl, CURLOPT_POSTFIELDS, $post_data);
	// Set HTTP Header for POST request
	curl_setopt($crl, CURLOPT_HTTPHEADER,
		array(
		'Content-Type: application/json',
		'Connection: keep-alive',
		'accept: application/json',
		'API-KEY: '.wpct_oc_get_api_key()
		)
	);
	// Submit the POST request
	$result = curl_exec($crl);
	$success = true;
	// handle curl error
	if ($result === false) {
		$success = false;
	}
	// handle http error code
	if( $success ){
		$response = curl_getinfo($crl, CURLINFO_HTTP_CODE);
		if($response != 200) $success = false;
	}
	curl_close($crl);
	if($success) return json_decode($result, true);
	return $success;
}
