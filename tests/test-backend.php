<?php
/**
 * Class BackendTest
 *
 * @package httpbridge-tests
 */

use HTTP_BRIDGE\Backend;

/**
 * Backend test case.
 */
class BackendTest extends WP_UnitTestCase {
	/**
	 * Handles the last intercepted http request data.
	 *
	 * @var array
	 */
	private static $request;

	/**
	 * Test data provider.
	 *
	 * @return Backend[]
	 */
	public static function provider() {
		$template = array(
			'name'     => 'test-backend',
			'base_url' => 'https://example.coop',
			'headers'  => array(),
		);

		$content_types = self::content_types();
		foreach ( $content_types as $content_type ) {
			$backend = $template;

			$backend['headers'][] = array(
				'name'  => 'Content-Type',
				'value' => $content_type,
			);

			$backends[] = new Backend( $backend );
		}

		return $backends;
	}

	/**
	 * Content types data provider.
	 *
	 * @return string[]
	 */
	public static function content_types() {
		return array(
			'application/json',
			'application/x-www-form-urlencoded',
			'multipart/form-data',
			'text/plain',
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

	/**
	 * Set up test hooks.
	 */
	public function set_up() {
		parent::set_up();

		tests_add_filter( 'http_bridge_backends', array( self::class, 'provider' ), 10, 0 );
		tests_add_filter( 'pre_http_request', array( self::class, 'pre_http_request' ), 10, 3 );
	}

	/**
	 * Tear down test hooks.
	 */
	public function tear_down() {
		remove_filter( 'http_bridge_backends', array( self::class, 'provider' ), 10, 0 );
		remove_filter( 'pre_http_request', array( self::class, 'pre_http_request' ), 10, 3 );

		parent::tear_down();
	}

	public function test_hook() {
		$backends = apply_filters( 'http_bridge_backends', array() );

		$this->assertEquals( 4, count( $backends ) );

		$content_types = array_map(
			function ( $b ) {
				return $b->content_type;
			},
			$backends,
		);

		$this->assertEqualSets(
			$this->content_types(),
			$content_types,
		);
	}

	public function test_schemas() {
		$backends = apply_filters( 'http_bridge_backends', array() );

		foreach ( $backends as $backend ) {
			$this->assertTrue( $backend->is_valid );
		}
	}

	public function test_http_requests() {
		$backends = apply_filters( 'http_bridge_backends', array() );

		foreach ( $backends as $backend ) {
			$payload = array( 'foo' => 'bar' );

			$response = $backend->get( '/api/query', $payload );
			$this->assertSame( 'https://example.coop/api/query?foo=bar', self::$request['url'] );

			if ( 'text/plain' === $backend->content_type ) {
				$payload = 'Foo is bar';
			}

			$response = $backend->post( '/api/endpoint', $payload );

			$this->assertTrue( ! is_wp_error( $response ) );
			$this->assertStringContainsString( $backend->headers['Content-Type'], self::$request['args']['headers']['Content-Type'] );
			$this->assertSame( 'https://example.coop/api/endpoint', self::$request['url'] );
		}
	}
}
