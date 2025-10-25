<?php

use HTTP_BRIDGE\REST_Settings_Controller;
use HTTP_BRIDGE\JWT;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

add_action( 'determine_current_user', 'http_bridge_jwt_determine_current_user', 1, 20 );
add_filter( 'rest_pre_dispatch', 'http_bridge_jwt_rest_pre_dispatch' );
add_action( 'rest_api_init', 'http_bridge_jwt_rest_api_init' );

function http_bridge_jwt_rest_api_init() {
	register_rest_route(
		'http-bridge/v1',
		'/jwt/auth',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'http_bridge_jwt_auth',
			'permission_callback' => 'http_bridge_jwt_auth_permission_callback',
		),
	);

	register_rest_route(
		'http-bridge/v1',
		'/jwt/validate',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'http_bridge_jwt_validate',
			'permission_callback' => 'http_bridge_jwt_validate_permission_callback',
		),
	);
}

/**
 * Authorization header getter.
 *
 * @return string Bearer token.
 *
 * @throws Exception If not authorization or if it's invalid.
 */
function http_bridge_jwt_authorization() {
	if ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) ) {
		$auth_header = sanitize_text_field(
			wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] )
		);
	} elseif ( isset( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) ) {
		$auth_header = sanitize_text_field(
			wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] )
		);
	}

	if ( ! isset( $auth_header ) ) {
		throw new Exception( 'Authorization header not found', 400 );
	}

	[$token] = sscanf( $auth_header, 'Bearer %s' );
	if ( ! $token ) {
		throw new Exception( 'Authorization header malformed', 400 );
	}

	return $token;
}

/**
 * Auth callback.
 *
 * @return array
 */
function http_bridge_jwt_auth() {
	global $http_bridge_jwt_user;

	$issuedAt  = time();
	$notBefore = $issuedAt;

	$expire = apply_filters(
		'http_bridge_jwt_auth_expire',
		$issuedAt + 60 * 60 * 24 * 7,
		$issuedAt
	);

	$claims = array(
		'iss'  => get_bloginfo( 'url' ),
		'iat'  => $issuedAt,
		'nbf'  => $notBefore,
		'exp'  => $expire,
		'data' => array(
			'user_id' => $http_bridge_jwt_user->data->ID,
		),
	);

	$token = ( new JWT() )->encode( $claims );

	return array(
		'token'        => $token,
		'user_email'   => $http_bridge_jwt_user->data->user_email,
		'user_login'   => $http_bridge_jwt_user->data->user_login,
		'display_name' => $http_bridge_jwt_user->data->display_name,
	);
}

/**
 * Validate callback.
 *
 * @return array
 */
function http_bridge_jwt_validate() {
	global $http_bridge_jwt_user;

	$token = http_bridge_jwt_authorization();

	return array(
		'token'        => $token,
		'user_email'   => $http_bridge_jwt_user->data->user_email,
		'user_login'   => $http_bridge_jwt_user->data->user_login,
		'display_name' => $http_bridge_jwt_user->data->display_name,
	);
}

/**
 * Performs auth requests permisison checks.
 *
 * @param WP_REST_Request Instance of the current REST request.
 *
 * @return boolean
 */
function http_bridge_jwt_auth_permission_callback( $request ) {
	$data = $request->get_json_params();

	if ( null === $data ) {
		return REST_Settings_Controller::bad_request( __( 'Invalid JSON data', 'http-bridge' ) );
	}

	if ( ! ( isset( $data['username'] ) && isset( $data['password'] ) ) ) {
		return REST_Settings_Controller::bad_request(
			__( 'Missing login credentials', 'http-bridge' )
		);
	}

	$user = wp_authenticate( $data['username'], $data['password'] );
	if ( is_wp_error( $user ) ) {
		return REST_Settings_Controller::unauthorized( __( 'Invalid credentials', 'http-bridge' ) );
	}

	global $http_bridge_jwt_user;
	$http_bridge_jwt_user = $user;

	return true;
}

/**
 * Performs validation requests permission checks.
 *
 * @return boolean
 */
function http_bridge_jwt_validate_permission_callback() {
	try {
		$token = http_bridge_jwt_authorization();
	} catch ( Exception $e ) {
		return REST_Settings_Controller::unauthorized( $e->getMessage() );
	}

	try {
		$payload = ( new JWT() )->decode( $token );
	} catch ( Exception ) {
		return REST_Settings_Controller::unauthorized(
			__( 'Invalid authorization token', 'http-bridge' )
		);
	} catch ( Error ) {
		return REST_Settings_Controller::internal_server_error(
			__( 'Internal Server Error', 'http-bridge' )
		);
	}

	if ( $payload['iss'] !== get_bloginfo( 'url' ) ) {
		return REST_Settings_Controller::unauthorized(
			__( 'The iss do not match with this server', 'http-bridge' )
		);
	}

	$now = time();
	if ( $payload['exp'] <= $now ) {
		return REST_Settings_Controller::unauthorized(
			__( 'The token is expired', 'http-bridge' )
		);
	}

	if ( $payload['nbf'] >= $now ) {
		return REST_Settings_Controller::unauthorized(
			__( 'The token is not valid yet', 'http-bridge' )
		);
	}

	if ( ! isset( $payload['data']['user_id'] ) ) {
		return REST_Settings_Controller::unauthorized(
			__( 'User ID not found in the token', 'http-bridge' )
		);
	}

	global $http_bridge_jwt_user;
	$http_bridge_jwt_user = get_user_by( 'ID', (int) $payload['data']['user_id'] );

	return true;
}

/**
 * Determine current user from bearer authentication.
 *
 * @param int|null $user_id Already identified user ID.
 *
 * @return int|null Identified user ID.
 */
function http_bridge_jwt_determine_current_user( $user_id ) {
	$rest_api_slug = rest_get_url_prefix();
	$requested_url = isset( $_SERVER['REQUEST_URI'] )
		? sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) )
		: '';

	$is_rest_request =
		( defined( 'REST_REQUEST' ) && REST_REQUEST ) ||
		strpos( $requested_url, $rest_api_slug );

	if ( $is_rest_request && $user_id ) {
		return $user_id;
	}

	$validate_uri = strpos( $requested_url, 'http-bridge/v1/jwt/validate' );

	if ( $validate_uri > 0 ) {
		return $user_id;
	}

	try {
		$auth = http_bridge_jwt_authorization();
	} catch ( Exception ) {
		return $user_id;
	}

	try {
		$payload = ( new JWT() )->decode( $auth );
	} catch ( Exception $e ) {
		if ( $e->getMessage() === 'Invalid token format' ) {
			global $http_bridge_jwt_auth_error;
			$http_bridge_jwt_auth_error = REST_Settings_Controller::unauthorized(
				$e->getMessage(),
				$e->getCode()
			);
		}

		return $user_id;
	} catch ( Error ) {
		return $user_id;
	}

	return (int) $payload['data']['user_id'];
}

/**
 * Abort rest dispatches if auth errors.
 *
 * @return object|WP_Error REST Request instance.
 */
function http_bridge_jwt_rest_pre_dispatch( $result ) {
	global $http_bridge_jwt_auth_error;

	if ( is_wp_error( $result ) || is_wp_error( $http_bridge_jwt_auth_error ) ) {
		return $http_bridge_jwt_auth_error;
	}

	return $result;
}
