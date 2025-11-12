<?php
/**
 * Class Credential
 *
 * @package httpbridge
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals
// phpcs:disable WordPress.WP.I18n.TextDomainMismatch

namespace HTTP_BRIDGE;

use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * Http credential object.
 */
class Credential {

	/**
	 * Handles the oauth transient name. The transient will store
	 * ephemeral credential data used on oath redirections.
	 *
	 * @var string
	 */
	private const TRANSIENT = 'http-bridge-oauth-credential';

	/**
	 * Credential json schema getter.
	 *
	 * @return array
	 */
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

	/**
	 * OAuth transient credential getter.
	 *
	 * @return Credential|null
	 */
	public static function get_transient() {
		$data = get_transient( static::TRANSIENT );

		if ( ! $data ) {
			wp_die( esc_html( __( 'Invalid oatuh redirect request', 'http-bridge' ) ) );
			return;
		} else {
			delete_transient( static::TRANSIENT );
		}

		$credential = new static( $data );
		if ( ! $credential->is_valid ) {
			return;
		}

		return $credential;
	}

	/**
	 * Handles credential data.
	 *
	 * @var array
	 */
	private $data;

	/**
	 * Credential constructor. Apply a data validation before before it is stored
	 * on the object.
	 *
	 * @param array $data Credential data.
	 */
	public function __construct( $data ) {
		$this->data = wpct_plugin_sanitize_with_schema( $data, static::schema() );
	}

	/**
	 * Object properties access interceptor. Proxies object properties to
	 * data attributes and performs some access control to values.
	 *
	 * @param string $name Property name.
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		switch ( $name ) {
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

	/**
	 * Gets the credential HTTP authorization.
	 *
	 * @return mixed
	 */
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

	/**
	 * Gets the OAuth authorization URL of the credential.
	 *
	 * @param string $verb Auth action to be performed (token, grant, revoke).
	 *
	 * @return string
	 */
	public function oauth_url( $verb ) {
		return apply_filters(
			'http_bridge_oauth_url',
			$this->oauth_url . '/' . $verb,
			$verb,
			$this
		);
	}

	/**
	 * Gets the OAuth redirect endpoint.
	 *
	 * @return string
	 */
	public function oauth_redirect_uri() {
		return get_rest_url() . 'http-bridge/v1/oauth/redirect';
	}

	/**
	 * Performs a token request to the the oauth url of the credential.
	 *
	 * @param array $query Request query.
	 *
	 * @return array|WP_Error
	 */
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

	/**
	 * Performs a revoke token request to the oauth url of the credential and removes
	 * stored tokens from the database.
	 */
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

	/**
	 * Updates credential tokens and write them to the database.
	 *
	 * @param array $tokens OAuth tokens.
	 *
	 * @return boolean Update result.
	 */
	private function update_tokens( $tokens ) {
		$data                 = $this->data;
		$data['enabled']      = true;
		$data['access_token'] = $tokens['access_token'];
		$data['expires_at']   = ( $tokens['expires_in'] ?? 0 ) + time() - 10;

		if ( isset( $tokens['refresh_token'] ) ) {
			$data['refresh_token'] = $tokens['refresh_token'];

			if ( isset( $tokens['refresh_token_expires_in'] ) ) {
				$data['refresh_token_expires_at'] = $tokens['refresh_token_expires_in'] + time() - 10;
			}
		}

		$credential = new static( $data );
		return $credential->save();
	}

	/**
	 * Refresh oauth access token.
	 *
	 * @return string|null Renewed access token, or null.
	 */
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

	/**
	 * Credential's access token public getter.
	 *
	 * @return string|null
	 */
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

	/**
	 * Revokes credential oauth tokens and remove them from the database.
	 *
	 * @return boolean Revoke result.
	 */
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

		set_transient( static::TRANSIENT, $this->data, 600 );
		return true;
	}

	/**
	 * OAuth HTTP redirection callback. The method will be executed by
	 * a transient credential on the authorization flow after a redirection
	 * to the oauth endpoint.
	 *
	 * @param REST_Request $request Request object.
	 *
	 * @return boolean
	 */
	public function oauth_redirect_callback( $request ) {
		if ( ! $this->is_valid ) {
			return false;
		}

		$tokens = $this->oauth_token_request(
			array(
				'grant_type'   => 'authorization_code',
				'code'         => $request['code'],
				'redirect_uri' => $this->oauth_redirect_uri(),
			)
		);

		if ( ! $tokens || is_wp_error( $tokens ) ) {
			wp_die( esc_html( __( 'Invalid oatuh redirect request', 'http-bridge' ) ) );
			return false;
		}

		return $this->update_tokens( $tokens );
	}

	/**
	 * Credential's data getter.
	 *
	 * @return array|null
	 */
	public function data() {
		if ( ! $this->is_valid ) {
			return null;
		}

		return $this->data;
	}

	/**
	 * Persist the credential on the database.
	 *
	 * @return boolean Database write result.
	 */
	public function save() {
		if ( ! $this->is_valid ) {
			return false;
		}

		$setting = Http_Setting::setting();
		if ( ! $setting ) {
			return false;
		}

		$credentials = $setting->credentials;
		if ( ! wp_is_numeric_array( $credentials ) ) {
			return false;
		}

		$index = array_search( $this->name, array_column( $credentials, 'name' ), true );

		if ( false === $index ) {
			$credentials[] = $this->data;
		} else {
			$credentials[ $index ] = $this->data;
		}

		$setting->credentials = $credentials;

		return true;
	}

	/**
	 * Removes the credential from the database.
	 *
	 * @retun boolean Database deletion result.
	 */
	public function delete() {
		if ( $this->is_valid ) {
			return false;
		}

		$setting = Http_Setting::setting();
		if ( ! $setting ) {
			return false;
		}

		$credentials = $setting->credentials;
		if ( ! wp_is_numeric_array( $credentials ) ) {
			return false;
		}

		$index = array_search( $this->name, array_column( $credentials, 'name' ), true );

		if ( false === $index ) {
			return false;
		}

		array_splice( $credentials, $index, 1 );
		$setting->credentials = $credentials;

		return true;
	}
}
