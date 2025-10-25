<?php

namespace HTTP_BRIDGE;

use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

class Credential {

	protected const transient = 'http-bridge-oauth-credential';

	public static function schema() {
		return array(
			'$schema' => 'http://json-schema.org/draft-04/schema#',
			'title'   => 'http-credential',
			'oneOf'   => array(
				array(
					'title'                => 'basic-credential',
					'type'                 => 'object',
					'properties'           => array(
						'name'          => array(
							'title'       => _x(
								'Name',
								'Credential schema',
								'http-bridge'
							),
							'description' => __(
								'Unique name of the credential',
								'http-bridge'
							),
							'type'        => 'string',
							'minLength'   => 1,
						),
						'schema'        => array(
							'title'   => _x(
								'Schema',
								'Credential schema',
								'http-bridge'
							),
							'type'    => 'string',
							'enum'    => array( 'Basic', 'Token', 'URL' ),
							'default' => 'Basic',
						),
						'client_id'     => array(
							'title' => _x(
								'Client ID',
								'Credential schema',
								'http-bridge'
							),
							'type'  => 'string',
						),
						'client_secret' => array(
							'title' => _x(
								'Client secret',
								'Credential schema',
								'http-bridge'
							),
							'type'  => 'string',
						),
					),
					'required'             => array(
						'name',
						'schema',
						'client_id',
						'client_secret',
					),
					'additionalProperties' => false,
				),
				array(
					'title'                => 'digest-credential',
					'type'                 => 'object',
					'properties'           => array(
						'name'          => array(
							'title'       => _x(
								'Name',
								'Credential schema',
								'http-bridge'
							),
							'description' => __(
								'Unique name of the credential',
								'http-bridge'
							),
							'type'        => 'string',
							'minLength'   => 1,
						),
						'schema'        => array(
							'title' => _x(
								'Schema',
								'Credential schema',
								'http-bridge'
							),
							'type'  => 'string',
							'enum'  => array( 'Digest' ),
						),
						'client_id'     => array(
							'title' => _x(
								'Client ID',
								'Credential schema',
								'http-bridge'
							),
							'type'  => 'string',
						),
						'client_secret' => array(
							'title' => _x(
								'Client secret',
								'Credential schema',
								'http-bridge'
							),
							'type'  => 'string',
						),
						'realm'         => array(
							'title' => _x(
								'Realm',
								'Credential schema',
								'http-bridge'
							),
							'type'  => 'string',
						),
					),
					'required'             => array(
						'name',
						'schema',
						'client_id',
						'client_secret',
						'realm',
					),
					'additionalProperties' => false,
				),
				array(
					'title'                => 'rpc-credential',
					'type'                 => 'object',
					'properties'           => array(
						'name'          => array(
							'title'       => _x(
								'Name',
								'Credential schema',
								'http-bridge'
							),
							'description' => __(
								'Unique name of the credential',
								'http-bridge'
							),
							'type'        => 'string',
							'minLength'   => 1,
						),
						'schema'        => array(
							'title' => _x(
								'Schema',
								'Credential schema',
								'http-bridge'
							),
							'type'  => 'string',
							'enum'  => array( 'RPC' ),
						),
						'client_id'     => array(
							'title' => _x(
								'User login',
								'Credential schema',
								'http-bridge'
							),
							'type'  => 'string',
						),
						'client_secret' => array(
							'title' => _x(
								'Password',
								'Credential schema',
								'http-bridge'
							),
							'type'  => 'string',
						),
						'database'      => array(
							'title' => _x(
								'Database',
								'Credential schema',
								'http-bridge'
							),
							'type'  => 'string',
						),
					),
					'required'             => array(
						'name',
						'schema',
						'client_id',
						'client_secret',
						'database',
					),
					'additionalProperties' => false,
				),
				array(
					'title'                => 'bearer-credential',
					'type'                 => 'object',
					'properties'           => array(
						'name'                     => array(
							'title'       => _x(
								'Name',
								'Credential schema',
								'http-bridge'
							),
							'description' => __(
								'Unique name of the credential',
								'http-bridge'
							),
							'type'        => 'string',
							'minLength'   => 1,
						),
						'schema'                   => array(
							'title' => _x(
								'Schema',
								'Credential schema',
								'http-bridge'
							),
							'type'  => 'string',
							'enum'  => array( 'Bearer' ),
						),
						'oauth_url'                => array(
							'title'  => _x(
								'Authorization URL',
								'Credential schema',
								'http-bridge'
							),
							'type'   => 'string',
							'format' => 'uri',
						),
						'client_id'                => array(
							'title' => _x(
								'Client ID',
								'Credential schema',
								'http-bridge'
							),
							'type'  => 'string',
						),
						'client_secret'            => array(
							'title' => _x(
								'Client secret',
								'Credential schema',
								'http-bridge'
							),
							'type'  => 'string',
						),
						'scope'                    => array(
							'title' => _x(
								'Scope',
								'Credential schema',
								'http-bridge'
							),
							'type'  => 'string',
						),
						'access_token'             => array(
							'title'   => _x(
								'Access token',
								'Credential schema',
								'http-bridge'
							),
							'type'    => 'string',
							'default' => '',
							'public'  => false,
						),
						'expires_at'               => array(
							'title'   => _x(
								'Expires at',
								'Credential schema',
								'http-bridge'
							),
							'type'    => 'integer',
							'default' => 0,
							'public'  => false,
						),
						'refresh_token'            => array(
							'title'   => _x(
								'Refresh token',
								'Credential schema',
								'http-bridge'
							),
							'type'    => 'string',
							'default' => '',
							'public'  => false,
						),
						'refresh_token_expires_at' => array(
							'title'   => _x(
								'Refresh token expires at',
								'Credential schema',
								'http-bridge'
							),
							'type'    => 'integer',
							'default' => 0,
							'public'  => false,
						),
					),
					'required'             => array(
						'name',
						'schema',
						'oauth_url',
						'client_id',
						'client_secret',
						// 'scope',
						'access_token',
						'expires_at',
						'refresh_token',
					),
					'additionalProperties' => true,
				),
			),
		);
	}

	/**
	 * Ephemeral credential registration as an interceptor to allow
	 * api fetch, ping and introspection with non registered credentials.
	 *
	 * @param array $data Credential data.
	 */
	public static function temp_registration( $data ) {
		if ( ! $data ) {
			return;
		}

		add_filter(
			'http_bridge_credentials',
			static function ( $credentials ) use ( $data ) {
				foreach ( $credentials as $candidate ) {
					if ( $candidate->name === $data['name'] ) {
						$credential = $candidate;
					}
				}

				if ( ! isset( $credential ) ) {
					$credential = new static( $data );

					if ( $credential->is_valid ) {
						$credentials[] = $credential;
					}
				}

				return $credentials;
			},
			99,
			2
		);
	}

	public static function get_transient() {
		$data = get_transient( static::transient );

		if ( ! $data ) {
			wp_die( __( 'Invalid oatuh redirect request', 'http-bridge' ) );
			return;
		} else {
			delete_transient( static::transient );
		}

		$credential = new static( $data );
		if ( ! $credential->is_valid ) {
			return;
		}

		return $credential;
	}

	protected $data;

	protected $id;

	public function __construct( $data ) {
		$this->data = wpct_plugin_sanitize_with_schema( $data, static::schema() );
	}

	public function __get( $name ) {
		switch ( $name ) {
			case 'id':
				return $this->id;
			case 'is_valid':
				return ! is_wp_error( $this->data );
			case 'client_id':
				if ( ! $this->is_valid ) {
					return;
				}

				return $this->data['client_id'] ?? $this->data['user'];
			case 'client_secret':
				if ( ! $this->is_valid ) {
					return;
				}

				return $this->data['client_secret'] ?? $this->data['password'];
			case 'realm':
				if ( ! $this->is_valid ) {
					return;
				}

				return $this->data['realm'] ??
					( $this->data['database'] ?? $this->data['scope'] );
			case 'access_token':
			case 'refresh_token':
				return;
			case 'authorized':
				return $this->is_valid && ! empty( $this->data['access_token'] );
			default:
				if ( ! $this->is_valid ) {
					return;
				}

				return $this->data[ $name ] ?? null;
		}
	}

	public function authorization() {
		switch ( $this->schema ) {
			case 'RPC':
				return array(
					$this->database,
					$this->client_id,
					$this->client_secret,
				);
			case 'Bearer':
				return 'Bearer ' . $this->get_access_token();
			case 'Basic':
				return 'Basic ' .
					base64_encode( "{$this->client_id}:{$this->client_secret}" );
			case 'Token':
				return "token {$this->client_id}:{$this->client_secret}";
			case 'URL':
				return "{$this->client_id}:{$this->client_secret}";
		}
	}

	public function oauth_url( $verb ) {
		return apply_filters(
			'http_bridge_oauth_url',
			$this->oauth_url . '/' . $verb,
			$verb,
			$this
		);
	}

	public function oauth_redirect_uri() {
		return get_rest_url() . 'http-bridge/v1/oauth/redirect';
	}

	private function oauth_token_request( $query ) {
		$url = $this->oauth_url( 'token' );

		$query['client_id']     = $this->client_id;
		$query['client_secret'] = $this->client_secret;

		$response = http_bridge_post(
			$url,
			$query,
			array(
				'Content-Type' => 'application/x-www-form-urlencoded',
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$data = $response['data'];

		if ( isset( $data['error'] ) ) {
			return new WP_Error( $data['error'] );
		}

		return $data;
	}

	private function revoke_refresh_token() {
		if ( ! empty( $this->data['refresh_token'] ) ) {
			$url   = $this->oauth_url( 'token/revoke' );
			$query = array( 'token' => $this->data['refresh_token'] );

			$response = http_bridge_post(
				$url,
				$query,
				array(
					'Content-Type' => 'application/x-www-form-urlencoded',
				)
			);

			// if (is_wp_error($response)) {
			// return false;
			// }
		}

		return $this->update_tokens(
			array(
				'access_token'             => '',
				'refresh_token'            => '',
				'expires_at'               => 0,
				'refresh_token_expires_at' => 0,
			)
		);
	}

	private function update_tokens( $tokens ) {
		$data                 = $this->data;
		$data['enabled']      = true;
		$data['access_token'] = $tokens['access_token'];
		$data['expires_at']   = ( $tokens['expires_in'] ?? 0 ) + time() - 10;

		if ( isset( $tokens['refresh_token'] ) ) {
			$data['refresh_token'] = $tokens['refresh_token'];

			if ( isset( $tokens['refresh_token_expires_in'] ) ) {
				$data['refresh_token_expires_at'] =
					$tokens['refresh_token_expires_in'] + time() - 10;
			}
		}

		$credential = new static( $data );
		return $credential->save();
	}

	private function refresh_access_token() {
		if ( ! $this->is_valid || empty( $this->data['refresh_token'] ) ) {
			return;
		}

		$tokens = $this->oauth_token_request(
			array(
				'grant_type'    => 'refresh_token',
				'refresh_token' => $this->data['refresh_token'],
			)
		);

		if ( $this->update_tokens( $tokens ) ) {
			return $tokens['access_token'];
		}
	}

	public function get_access_token() {
		if ( ! $this->is_valid ) {
			return;
		}

		$access_token = $this->data['access_token'];
		if ( ! $access_token ) {
			return;
		}

		if ( $this->expires_at <= time() ) {
			$expires_at = $this->refresh_token_expires_at;
			if ( $expires_at && $expires_at <= time() ) {
				return;
			}

			return $this->refresh_access_token();
		}

		return $access_token;
	}

	public function oauth_revoke() {
		if ( ! $this->is_valid ) {
			return false;
		}

		if ( ! empty( $this->data['refresh_token'] ) ) {
			$result = $this->revoke_refresh_token();

			if ( ! $result ) {
				return new WP_Error( 'internal_server_error' );
			}
		}

		return true;
	}

	public function oauth_grant_transient() {
		if ( ! $this->is_valid ) {
			return new WP_Error( 'invalid_credential' );
		}

		set_transient( static::transient, $this->data, 600 );
		return true;
	}

	public function oauth_redirect_callback( $request ) {
		if ( ! $this->is_valid ) {
			return;
		}

		$tokens = $this->oauth_token_request(
			array(
				'grant_type'   => 'authorization_code',
				'code'         => $request['code'],
				'redirect_uri' => $this->oauth_redirect_uri(),
			)
		);

		if ( ! $tokens || is_wp_error( $tokens ) ) {
			wp_die( __( 'Invalid oatuh redirect request', 'http-bridge' ) );
			return;
		}

		return $this->update_tokens( $tokens );
	}

	public function data() {
		if ( ! $this->is_valid ) {
			return;
		}

		return $this->data;
	}

	public function save() {
		if ( ! $this->is_valid ) {
			return false;
		}

		$setting = Settings_Store::setting( 'general' );
		if ( ! $setting ) {
			return false;
		}

		$credentials = $setting->credentials;
		if ( ! wp_is_numeric_array( $credentials ) ) {
			return false;
		}

		$index = array_search( $this->name, array_column( $credentials, 'name' ) );

		if ( $index === false ) {
			$credentials[] = $this->data;
		} else {
			$credentials[ $index ] = $this->data;
		}

		$setting->credentials = $credentials;

		return true;
	}

	public function delete() {
		if ( $this->is_valid ) {
			return false;
		}

		$setting = Settings_Store::setting( 'general' );
		if ( ! $setting ) {
			return false;
		}

		$credentials = $setting->credentials;
		if ( ! wp_is_numeric_array( $credentials ) ) {
			return false;
		}

		$index = array_search( $this->name, array_column( $credentials, 'name' ) );

		if ( $index === false ) {
			return false;
		}

		array_splice( $credentials, $index, 1 );
		$setting->credentials = $credentials;

		return true;
	}
}
