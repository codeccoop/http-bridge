<?php
/**
 * Class CredentialTest
 *
 * @package httpbridge-tests
 */

use HTTP_BRIDGE\Backend;
use HTTP_BRIDGE\Credential;

/**
 * Credential test case.
 */
class CredentialTest extends WP_UnitTestCase {
	/**
	 * Handles the last intercepted http request data.
	 *
	 * @var array
	 */
	private static $request;

	/**
	 * Test data provider.
	 *
	 * @return Credential[]
	 */
	public static function provider() {
		$credentials = array(
			array(
				'name'          => 'test-basic-credential',
				'schema'        => 'Basic',
				'client_id'     => 'foo',
				'client_secret' => 'bar',
			),
			array(
				'name'          => 'test-url-credential',
				'schema'        => 'URL',
				'client_id'     => 'foo',
				'client_secret' => 'bar',
			),
			array(
				'name'          => 'test-token-credential',
				'schema'        => 'Token',
				'client_id'     => 'foo',
				'client_secret' => 'bar',
			),
			array(
				'name'          => 'test-digest-credential',
				'schema'        => 'Digest',
				'client_id'     => 'foo',
				'client_secret' => 'bar',
				'realm'         => 'foobar',
			),
			array(
				'name'          => 'test-rpc-credential',
				'schema'        => 'RPC',
				'client_id'     => 'foo',
				'client_secret' => 'bar',
				'database'      => 'foobar',
			),
			array(
				'name'         => 'test-bearer-credential',
				'schema'       => 'Bearer',
				'access_token' => 'access-token',
				'expires_at'   => time() + 3600,
			),
			array(
				'name'          => 'test-oauth-credential',
				'schema'        => 'OAuth',
				'client_id'     => 'foo',
				'client_secret' => 'bar',
				'scope'         => 'foobar',
				'oauth_url'     => 'https://auth.example.coop',
				'access_token'  => 'access-token',
				'expires_at'    => time() + 3600,
				'refresh_token' => 'refresh-token',
			),
		);

		return array_map(
			function ( $data ) {
				return new Credential( $data );
			},
			$credentials
		);
	}

	/**
	 * HTTP requests interceptor. Prevent test to access the network and store the request arguments
	 * on the static $request attribute.
	 *
	 * @param mixed  $pre Initial pre hook value.
	 * @param array  $args Request arguments.
	 * @param string $url Request URL.
	 *
	 * @return array
	 */
	public static function pre_http_request( $pre, $args, $url ) {
		self::$request = array(
			'args' => $args,
			'url'  => $url,
		);

		return array(
			'response'      => array(
				'code'    => 200,
				'message' => 'Success',
			),
			'headers'       => array( 'Content-Type' => 'application/json' ),
			'cookies'       => array(),
			'body'          => '{"success":true}',
			'http_response' => null,
		);
	}

	public function set_up() {
		parent::set_up();

		add_filter( 'http_bridge_credentials', array( self::class, 'provider' ), 10, 0 );
		add_filter( 'pre_http_request', array( self::class, 'pre_http_request' ), 10, 3 );
	}

	public function tear_down() {
		remove_filter( 'http_bridge_credentials', array( self::class, 'provider' ), 10, 0 );
		remove_filter( 'pre_http_request', array( self::class, 'pre_http_request' ), 10, 3 );

		parent::tear_down();
	}

	public function test_hook() {
		$credentials = apply_filters( 'http_bridge_credentials', array() );

		$this->assertEquals( 7, count( $credentials ) );

		$schemas = array_map(
			function ( $c ) {
				return $c->schema;
			},
			$credentials
		);

		$this->assertEqualSets(
			array(
				'Basic',
				'URL',
				'Token',
				'Digest',
				'RPC',
				'Bearer',
				'OAuth',
			),
			$schemas,
		);
	}

	public function test_schemas() {
		$credentials = apply_filters( 'http_bridge_credentials', array() );

		foreach ( $credentials as $credential ) {
			$this->assertTrue( $credential->is_valid );
		}
	}

	public function test_authorization() {
		$credentials = apply_filters( 'http_bridge_credentials', array() );

		foreach ( $credentials as $credential ) {
			$authorization = $credential->authorization();

			switch ( $credential->schema ) {
				case 'Basic':
					$this->assertSame(
						$authorization,
						'Basic ' . base64_encode( "{$credential->client_id}:{$credential->client_secret}" )
					);

					break;
				case 'URL':
					$this->assertSame( $authorization, "{$credential->client_id}:{$credential->client_secret}" );
					break;
				case 'Token':
					$this->assertSame( $authorization, "token {$credential->client_id}:{$credential->client_secret}" );
					break;
				case 'RPC':
					$this->assertEqualSets(
						$authorization,
						array(
							$credential->client_id,
							$credential->client_secret,
							$credential->database,
						)
					);

					break;
				case 'Bearer':
				case 'OAuth':
					$this->assertSame( $authorization, 'Bearer ' . $credential->get_access_token() );
					break;
				default:
					$this->assertNull( $authorization );
			}
		}
	}

	public function test_authorized_backend() {
		$backend = new Backend(
			array(
				'name'     => 'test-authorized-backend',
				'base_url' => 'https://example.coop',
				'headers'  => array(
					array(
						'name'  => 'Content-Type',
						'value' => 'application/json',
					),
				),
			)
		);

		$credentials = apply_filters( 'http_bridge_credentials', array() );
		foreach ( $credentials as $credential ) {
			$authorized = $backend->clone(
				array( 'credential' => $credential->name ),
			);

			$response = $authorized->get( '/api/endpoint', array( 'foo' => 'bar' ) );

			$this->assertTrue( ! is_wp_error( $response ) );

			switch ( $credential->schema ) {
				case 'Basic':
				case 'Token':
				case 'OAuth':
				case 'Bearer':
					$this->assertTrue( isset( self::$request['args']['headers']['Authorization'] ) );
					$this->assertSame( $credential->authorization(), self::$request['args']['headers']['Authorization'] );
					break;
				case 'URL':
					$this->assertStringContainsString( $credential->authorization(), self::$request['url'] );
					break;
			}
		}
	}
}
