<?php

namespace HTTP_BRIDGE;

use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit();
}

/**
 * HTTP backend connexión.
 */
class Backend {

	public static function schema() {
		return array(
			'$schema'              => 'http://json-schema.org/draft-04/schema#',
			'title'                => 'backend',
			'type'                 => 'object',
			'properties'           => array(
				'name'       => array(
					'type'      => 'string',
					'minLength' => 1,
				),
				'base_url'   => array(
					'type'   => 'string',
					'format' => 'uri',
				),
				'headers'    => array(
					'type'    => 'array',
					'items'   => array(
						'type'                 => 'object',
						'properties'           => array(
							'name'  => array( 'type' => 'string' ),
							'value' => array( 'type' => 'string' ),
						),
						'required'             => array( 'name', 'value' ),
						'additionalProperties' => false,
					),
					'default' => array(),
				),
				'credential' => array( 'type' => 'string' ),
			),
			'required'             => array( 'name', 'base_url', 'headers' ),
			'additionalProperties' => false,
		);
	}

	/**
	 * Ephemeral backend registration as an interceptor to allow
	 * the use of non registered backends.
	 *
	 * @param array $data Backend data.
	 */
	public static function temp_registration( $data ) {
		if ( empty( $data ) || ! isset( $data['name'] ) ) {
			return;
		}

		add_filter(
			'http_bridge_backends',
			static function ( $backends ) use ( $data ) {
				foreach ( $backends as $candidate ) {
					if ( $candidate->name === $data['name'] ) {
						$backend = $candidate;
						break;
					}
				}

				if ( ! isset( $backend ) ) {
					$backend = new static( $data );

					if ( $backend->is_valid ) {
						$backends[] = $backend;
					}
				}

				return $backends;
			},
			99,
			1
		);
	}

	/**
	 * Handle backend data.
	 *
	 * @var array|null $data Backend data.
	 */
	private $data;

	/**
	 * Store backend data
	 */
	public function __construct( $data ) {
		$this->data = wpct_plugin_sanitize_with_schema( $data, static::schema() );
	}

	/**
	 * Intercepts class attributes accesses and lookup on backend data.
	 *
	 * @param string $attr Attribute name.
	 *
	 * @return mixed Attribute value or null.
	 */
	public function __get( $attr ) {
		switch ( $attr ) {
			case 'is_valid':
				return ! is_wp_error( $this->data );
			case 'headers':
				return $this->headers();
			case 'content_type':
				return $this->content_type();
			case 'credential':
				return $this->credential();
			default:
				if ( ! $this->is_valid ) {
					return;
				}

				return $this->data[ $attr ] ?? null;
		}
	}

	/**
	 * Gets backend default headers.
	 *
	 * @return array $headers Backend headers.
	 */
	private function headers() {
		if ( ! $this->is_valid ) {
			return array();
		}

		$headers = array();
		foreach ( $this->data['headers'] as $header ) {
			$headers[ trim( $header['name'] ) ] = trim( $header['value'] );
		}

		if ( $credential = $this->credential ) {
			$authorization = $credential->authorization();

			if (
				$credential->schema !== 'URL' &&
				$authorization &&
				is_string( $authorization )
			) {
				$headers['Authorization'] = $authorization;
			}
		}

		return apply_filters( 'http_bridge_backend_headers', $headers, $this );
	}

	/**
	 * Gets backend default request body content type encoding schema.
	 *
	 * @return string|null Encoding schema.
	 */
	private function content_type() {
		if ( ! $this->is_valid ) {
			return;
		}

		$headers = $this->headers();
		return Http_Client::get_content_type( $headers );
	}

	private function credential() {
		if ( ! $this->is_valid || empty( $this->data['credential'] ) ) {
			return;
		}

		$credentials = apply_filters( 'http_bridge_credentials', array() );
		foreach ( $credentials as $credential ) {
			if ( $credential->name === $this->data['credential'] ) {
				return $credential;
			}
		}
	}

	/**
	 * Gets backend absolute URL.
	 *
	 * @param string $path URL relative path.
	 *
	 * @return string $url Absolute URL.
	 */
	public function url( $path = '' ) {
		if ( ! $this->is_valid ) {
			return;
		}

		$base_url = preg_replace( '/\/+$/', '', $this->base_url ?? '' );

		$url_parsed = wp_parse_url( $base_url );

		if ( isset( $url_parsed['path'] ) ) {
			$base_path = preg_replace( '/^\/+/', '', $url_parsed['path'] );
			$path      = preg_replace(
				'/^\/*' . preg_quote( $base_path, '/' ) . '/',
				'',
				$path
			);
		} else {
			$url_parsed['path'] = '';
		}

		if ( $credential = $this->credential ) {
			if ( $credential->schema === 'URL' ) {
				$authorization = $credential->authorization();
				$base_url      = "{$url_parsed['scheme']}://{$authorization}@{$url_parsed['host']}";

				if ( isset( $url_parsed['port'] ) ) {
					$base_url .= ':' . $url_parsed['port'];
				}

				if ( isset( $url_parsed['path'] ) ) {
					$base_url .= $url_parsed['path'];
				}
			}
		}

		$path_parsed = wp_parse_url( (string) $path );
		$query       = $path_parsed['query'] ?? null;

		if ( isset( $path_parsed['path'] ) ) {
			$path = preg_replace( '/^\/+/', '', $path_parsed['path'] );
		} else {
			$path = '';
		}

		$url = $base_url . '/' . $path;

		if ( $query ) {
			$url .= '?' . $query;
		}

		return apply_filters( 'http_bridge_backend_url', $url, $this );
	}

	private function handle_response( $response_or_error ) {
		if ( ! is_wp_error( $response_or_error ) ) {
			return $response_or_error;
		} else {
			$error = $response_or_error;
		}

		$credential = $this->credential();
		if ( ! $credential || $credential->schema !== 'Digest' ) {
			return $error;
		}

		$error_data = $error->get_error_data();
		$response   = $error_data['response'];

		if ( $response['response']['code'] !== 401 ) {
			return $error;
		}

		$digest_header = $response['headers']['WWW-Authenticate'] ?? null;
		if ( ! $digest_header ) {
			return $error;
		}

		$fields = array(
			'realm'  => null,
			'nonce'  => null,
			'opaque' => null,
		);
		foreach ( array_keys( $fields ) as $field ) {
			if (
				! preg_match( "/{$field}=\"([^\"]+)\"/", $digest_header, $matches )
			) {
				return $error;
			}

			$fields[ $field ] = $matches[1];
		}

		if ( $fields['realm'] !== $credential->realm ) {
			return $error;
		}

		$request    = $error_data['request'];
		$parsed_url = wp_parse_url( $request['url'] );
		$uri        = $parsed_url['path'] ?? '';

		$a1       = md5(
			"{$credential->client_id}:{$credential->realm}:{$credential->client_secret}"
		);
		$a2       = md5( "{$this->method}:{$this->endpoint}" );
		$response = md5( "{$a1}:{$fields['nonce']}:{$a2}" );

		$authorization = "Digest username=\"{$credential->client_id}\" realm=\"{$credential->realm}\" nonce=\"{$fields['nonce']}\" opaque=\"{$fields['opaque']}\" uri=\"{$uri}\" response=\"{$response}\"";

		$request['args']['headers']['Authorization'] = $authorization;
		return wp_remote_request( $request['url'], $request['args'] );
	}

	public function head( $endpoint, $params = array(), $headers = array() ) {
		if ( ! $this->is_valid ) {
			return new WP_Error( 'invalid_backend' );
		}

		$url      = $this->url( $endpoint );
		$headers  = array_merge( $this->headers(), (array) $headers );
		$response = http_bridge_head( $url, $params, $headers );
		return $this->handle_response( $response );
	}

	/**
	 * Performs a GET HTTP request to the backend.
	 *
	 * @param string $endpoint Target backend endpoint as relative path.
	 * @param array  $params URL query params.
	 * @param array  $headers Additional HTTP headers.
	 * @param array  $args Additional request args.
	 *
	 * @return array|WP_Error Request response.
	 */
	public function get( $endpoint, $params = array(), $headers = array(), $args = array() ) {
		if ( ! $this->is_valid ) {
			return new WP_Error( 'invalid_backend' );
		}

		$url      = $this->url( $endpoint );
		$headers  = array_merge( $this->headers(), (array) $headers );
		$response = http_bridge_get( $url, $params, $headers, $args );

		return $this->handle_response( $response );
	}

	/**
	 * Performs a POST HTTP request to the backend.
	 *
	 * @param string $endpoint Target backend endpoint as relative path.
	 * @param array  $params URL query params.
	 * @param array  $headers Additional HTTP headers.
	 * @param array  $files Map with names and filepaths.
	 * @param array  $args Additional request args.
	 *
	 * @return array|WP_Error Request response.
	 */
	public function post(
		$endpoint,
		$data = array(),
		$headers = array(),
		$files = array(),
		$args = array()
	) {
		if ( ! $this->is_valid ) {
			return new WP_Error( 'invalid_backend' );
		}

		$url     = $this->url( $endpoint );
		$headers = array_merge( $this->headers(), (array) $headers );
		return http_bridge_post( $url, $data, $headers, $files, $args );
	}

	/**
	 * Performs a PUT HTTP request to the backend.
	 *
	 * @param string $endpoint Target backend endpoint as relative path.
	 * @param array  $params URL query params.
	 * @param array  $headers Additional HTTP headers.
	 * @param array  $files Map with names and filepaths.
	 * @param array  $args Additional request args.
	 *
	 * @return array|WP_Error Request response.
	 */
	public function put(
		$endpoint,
		$data = array(),
		$headers = array(),
		$files = array(),
		$args = array()
	) {
		if ( ! $this->is_valid ) {
			return new WP_Error( 'invalid_backend' );
		}

		$url     = $this->url( $endpoint );
		$headers = array_merge( $this->headers(), (array) $headers );
		return http_bridge_put( $url, $data, $headers, $files, $args );
	}

	/**
	 * Performs a PATCH HTTP request to the backend.
	 *
	 * @param string $endpoint Target backend endpoint as relative path.
	 * @param array  $params URL query params.
	 * @param array  $headers Additional HTTP headers.
	 * @param array  $files Map with names and filepaths.
	 * @param array  $args Additional request args.
	 *
	 * @return array|WP_Error Request response.
	 */
	public function patch(
		$endpoint,
		$data = array(),
		$headers = array(),
		$files = array(),
		$args = array()
	) {
		if ( ! $this->is_valid ) {
			return new WP_Error( 'invalid_backend' );
		}

		$url     = $this->url( $endpoint );
		$headers = array_merge( $this->headers(), (array) $headers );
		return http_bridge_patch( $url, $data, $headers, $files, $args );
	}

	/**
	 * Performs a DELETE HTTP request to the backend.
	 *
	 * @param string $endpoint Target backend endpoint as relative path.
	 * @param array  $params URL query params.
	 * @param array  $headers Additional HTTP headers.
	 * @param array  $args Additional request args.
	 *
	 * @return array|WP_Error Request response.
	 */
	public function delete( $endpoint, $params = array(), $headers = array(), $args = array() ) {
		if ( ! $this->is_valid ) {
			return new WP_Error( 'invalid_backend' );
		}

		$url     = $this->url( $endpoint );
		$headers = array_merge( $this->headers(), (array) $headers );
		return http_bridge_delete( $url, $params, $headers, $args );
	}

	public function clone( $partial = array() ) {
		if ( ! $this->is_valid ) {
			return $this;
		}

		$data = array_merge( $this->data, $partial );
		return new static( $data );
	}

	public function data() {
		if ( ! $this->is_valid ) {
			return;
		}

		return $this->data;
	}
}
