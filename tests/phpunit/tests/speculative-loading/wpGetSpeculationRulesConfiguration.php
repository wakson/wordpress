<?php
/**
 * Tests for the wp_get_speculation_rules_configuration() function.
 *
 * @package WordPress
 * @subpackage Speculative Loading
 */

/**
 * @group speculative-loading
 * @covers ::wp_get_speculation_rules_configuration
 */
class Tests_Speculative_Loading_wpGetSpeculationRulesConfiguration extends WP_UnitTestCase {

	/**
	 * Tests that the default configuration is the expected value.
	 *
	 * @ticket 62503
	 */
	public function test_wp_get_speculation_rules_configuration_default() {
		$filter_default = null;
		add_filter(
			'wp_speculation_rules_configuration',
			function ( $config ) use ( &$filter_default ) {
				$filter_default = $config;
				return $config;
			}
		);

		$config_default = wp_get_speculation_rules_configuration();

		// The filter default uses 'auto', but for the function result this is evaluated to actual mode and eagerness.
		$this->assertSame(
			array(
				'mode'      => 'auto',
				'eagerness' => 'auto',
			),
			$filter_default
		);
		$this->assertSame(
			array(
				'mode'      => 'prefetch',
				'eagerness' => 'conservative',
			),
			$config_default
		);
	}

	/**
	 * Tests that the configuration can be filtered and leads to the expected results.
	 *
	 * @ticket 62503
	 * @dataProvider data_wp_get_speculation_rules_configuration_filter
	 */
	public function test_wp_get_speculation_rules_configuration_filter( $filter_value, $expected ) {
		add_filter(
			'wp_speculation_rules_configuration',
			function () use ( $filter_value ) {
				return $filter_value;
			}
		);

		$this->assertSame( $expected, wp_get_speculation_rules_configuration() );
	}

	public function data_wp_get_speculation_rules_configuration_filter(): array {
		return array(
			'conservative prefetch'  => array(
				array(
					'mode'      => 'prefetch',
					'eagerness' => 'conservative',
				),
				array(
					'mode'      => 'prefetch',
					'eagerness' => 'conservative',
				),
			),
			'moderate prefetch'      => array(
				array(
					'mode'      => 'prefetch',
					'eagerness' => 'moderate',
				),
				array(
					'mode'      => 'prefetch',
					'eagerness' => 'moderate',
				),
			),
			'eager prefetch'         => array(
				array(
					'mode'      => 'prefetch',
					'eagerness' => 'eager',
				),
				array(
					'mode'      => 'prefetch',
					'eagerness' => 'eager',
				),
			),
			'conservative prerender' => array(
				array(
					'mode'      => 'prerender',
					'eagerness' => 'conservative',
				),
				array(
					'mode'      => 'prerender',
					'eagerness' => 'conservative',
				),
			),
			'moderate prerender'     => array(
				array(
					'mode'      => 'prerender',
					'eagerness' => 'moderate',
				),
				array(
					'mode'      => 'prerender',
					'eagerness' => 'moderate',
				),
			),
			'eager prerender'        => array(
				array(
					'mode'      => 'prerender',
					'eagerness' => 'eager',
				),
				array(
					'mode'      => 'prerender',
					'eagerness' => 'eager',
				),
			),
			'auto'                   => array(
				array(
					'mode'      => 'auto',
					'eagerness' => 'auto',
				),
				array(
					'mode'      => 'prefetch',
					'eagerness' => 'conservative',
				),
			),
			'auto mode only'         => array(
				array(
					'mode'      => 'auto',
					'eagerness' => 'eager',
				),
				array(
					'mode'      => 'prefetch',
					'eagerness' => 'eager',
				),
			),
			'auto eagerness only'    => array(
				array(
					'mode'      => 'prerender',
					'eagerness' => 'auto',
				),
				array(
					'mode'      => 'prerender',
					'eagerness' => 'conservative',
				),
			),
			'null'                   => array(
				null,
				null,
			),
			'false'                  => array(
				false,
				array(
					'mode'      => 'prefetch',
					'eagerness' => 'conservative',
				),
			),
			'true'                   => array(
				true,
				array(
					'mode'      => 'prefetch',
					'eagerness' => 'conservative',
				),
			),
			'missing mode'           => array(
				array(
					'eagerness' => 'eager',
				),
				array(
					'mode'      => 'prefetch',
					'eagerness' => 'eager',
				),
			),
			'missing eagerness'      => array(
				array(
					'mode' => 'prerender',
				),
				array(
					'mode'      => 'prerender',
					'eagerness' => 'conservative',
				),
			),
			'empty array'            => array(
				array(),
				array(
					'mode'      => 'prefetch',
					'eagerness' => 'conservative',
				),
			),
			'invalid mode'           => array(
				array(
					'mode'      => 'invalid',
					'eagerness' => 'eager',
				),
				array(
					'mode'      => 'prefetch',
					'eagerness' => 'eager',
				),
			),
			'invalid eagerness'      => array(
				array(
					'mode'      => 'prerender',
					'eagerness' => 'invalid',
				),
				array(
					'mode'      => 'prerender',
					'eagerness' => 'conservative',
				),
			),
			'invalid type'           => array(
				42,
				array(
					'mode'      => 'prefetch',
					'eagerness' => 'conservative',
				),
			),
		);
	}
}
