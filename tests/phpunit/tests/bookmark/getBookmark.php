<?php

/**
 * @group bookmark
 * @group getBookmark
 * @covers :get_bookmark
 */
class Tests_Bookmark_GetBookmark extends WP_UnitTestCase {
	/**
	 * Instance of the bookmark object.
	 *
	 * @var stdClass
	 */
	private static $bookmark;

	/**
	 * Setup the test environment before running the tests in this class.
	 *
	 * @param WP_UnitTest_Factory $factory Instance of the factory.
	 */
	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		unset( $GLOBALS['link'] );
		self::$bookmark = $factory->bookmark->create_and_get();
		wp_cache_delete( self::$bookmark->link_id, 'bookmark' );
	}

	/**
	 * Clean up the global and cache state after each test.
	 */
	public function tearDown() {
		unset( $GLOBALS['link'] );
		wp_cache_delete( self::$bookmark->link_id, 'bookmark' );

		parent::tearDown();
	}

	/**
	 * @dataProvider data_when_empty_bookmark
	 */
	public function test_should_return_null( $params ) {
		$params          = $this->init_func_params( $params, 0 );
		$actual_bookmark = get_bookmark( ...$params );

		$this->assertArrayNotHasKey( 'link', $GLOBALS );
		$this->assertNull( $actual_bookmark );

		// Should bypass the cache.
		$this->assertFalse( wp_cache_get( self::$bookmark->link_id, 'bookmark' ) );
	}

	/**
	 * @dataProvider data_when_empty_bookmark
	 */
	public function test_should_return_global_link_in_requested_output_format( $params ) {
		$GLOBALS['link'] = self::$bookmark;
		$params          = $this->init_func_params( $params, 0 );
		$actual_bookmark = get_bookmark( ...$params );

		$expected = $this->maybe_format_expected_data( $params, $GLOBALS['link'] );

		$this->assertArrayHasKey( 'link', $GLOBALS );
		$this->assertSame( $expected, $actual_bookmark );
		// Should bypass the cache.
		$this->assertFalse( wp_cache_get( self::$bookmark->link_id, 'bookmark' ) );
	}

	public function data_when_empty_bookmark() {
		return array(
			'unhappy path with null bookmark'    => array(
				array(
					'bookmark' => null,
				),
			),
			'with defaults'                      => array(
				array(
					'bookmark' => 0,
				),
			),
			'with non-default output'            => array(
				array(
					'bookmark' => 0,
					'output'   => ARRAY_A,
				),
			),
			'with non-default filter'            => array(
				array(
					'bookmark' => 0,
					'filter'   => 'display',
				),
			),
			'with non-default output and filter' => array(
				array(
					'bookmark' => 0,
					'output'   => ARRAY_N,
					'filter'   => 'display',
				),
			),
		);
	}

	/**
	 * @dataProvider data_when_instance_bookmark
	 */
	public function test_should_cache_bookmark_when_given_instance( $params ) {
		$params = $this->init_func_params( $params );

		// Check the cache does not exist before the test.
		$this->assertFalse( wp_cache_get( self::$bookmark->link_id, 'bookmark' ) );

		get_bookmark( ...$params );

		// Check the bookmark was cached.
		$actual_cache = wp_cache_get( self::$bookmark->link_id, 'bookmark' );
		$this->assertEquals( self::$bookmark, $actual_cache );
	}

	/**
	 * @dataProvider data_when_instance_bookmark
	 */
	public function test_should_return_in_requested_output_format_when_given_instance( $params ) {
		$params = $this->init_func_params( $params );

		$expected = $this->maybe_format_expected_data( $params );

		$actual_bookmark = get_bookmark( ...$params );

		$this->assertSame( $expected, $actual_bookmark );
	}

	public function data_when_instance_bookmark() {
		return array(
			'with defaults'                      => array(
				array(),
			),
			'with non-default output'            => array(
				array(
					'output' => ARRAY_A,
				),
			),
			'with non-default filter'            => array(
				array(
					'filter' => 'display',
				),
			),
			'with non-default output and filter' => array(
				array(
					'output' => ARRAY_N,
					'filter' => 'display',
				),
			),
		);
	}

	/**
	 * Initialize the get_bookmark's function parameters to match the order of the function's signature and
	 * reduce code in the tests.
	 *
	 * @param array        $params   Array of given function parameters.
	 * @param int|stdClass $bookmark Optional. Bookmark's cache key or instance.
	 *
	 * @return array An array of ordered parameters.
	 */
	private function init_func_params( array $params, $bookmark = null ) {
		// The defaults sets the order to match the function's parameters as well as setting the default values.
		$defaults = array(
			'bookmark' => self::$bookmark,
			'output'   => OBJECT,
			'filter'   => 'raw',
		);
		$params   = array_merge( $defaults, $params );

		// When given a bookmark, use it.
		if ( ! is_null( $bookmark ) ) {
			$params['bookmark'] = $bookmark;
		}

		// Strip out the keys. Why? The splat operator (...) does not work with associative arrays,
		// except for in PHP 8 where the keys are named arguments.
		return array_values( $params );
	}

	/**
	 * Maybe format the bookmark's expected data.
	 *
	 * @param array             $params   Array of given function parameters.
	 * @param int|stdClass|null $bookmark Optional. Bookmark's cache key or instance.
	 *
	 * @return array|stdClass bookmark's data.
	 */
	private function maybe_format_expected_data( array $params, $bookmark = null ) {
		if ( is_null( $bookmark ) ) {
			$bookmark = self::$bookmark;
		}

		switch ( $params[1] ) {
			case ARRAY_A:
			case ARRAY_N:
				$expected = get_object_vars( $bookmark );

				if ( ARRAY_N === $params[1] ) {
					$expected = array_values( $expected );
				}

				return $expected;
			default:
				return $bookmark;
		}
	}
}
