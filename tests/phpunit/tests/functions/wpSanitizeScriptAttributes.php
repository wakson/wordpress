<?php

/**
 * Test wp_sanitize_script_attributes().
 *
 * @group functions.php
 */
class Tests_Functions_wpSanitizeScriptAttributes extends WP_UnitTestCase {

	function test_sanitize_script_attributes_type_set() {
		add_theme_support( 'html5', array( 'script' ) );

		$this->assertSame(
			' type="application/javascript" src="https://DOMAIN.TLD/PATH/FILE.js" nomodule',
			wp_sanitize_script_attributes(
				array(
					'type'     => 'application/javascript',
					'src'      => 'https://DOMAIN.TLD/PATH/FILE.js',
					'async'    => false,
					'nomodule' => true,
				)
			)
		);

		remove_theme_support( 'html5' );

		$this->assertSame(
			' src="https://DOMAIN.TLD/PATH/FILE.js" type="application/javascript" nomodule',
			wp_sanitize_script_attributes(
				array(
					'src'      => 'https://DOMAIN.TLD/PATH/FILE.js',
					'type'     => 'application/javascript',
					'async'    => false,
					'nomodule' => true,
				)
			)
		);
	}

	function test_sanitize_script_attributes_type_not_set() {
		add_theme_support( 'html5', array( 'script' ) );

		$this->assertSame(
			' src="https://DOMAIN.TLD/PATH/FILE.js" nomodule',
			wp_sanitize_script_attributes(
				array(
					'src'      => 'https://DOMAIN.TLD/PATH/FILE.js',
					'async'    => false,
					'nomodule' => true,
				)
			)
		);

		remove_theme_support( 'html5' );

		$this->assertSame(
			' src="https://DOMAIN.TLD/PATH/FILE.js" nomodule type="text/javascript"',
			wp_sanitize_script_attributes(
				array(
					'src'      => 'https://DOMAIN.TLD/PATH/FILE.js',
					'async'    => false,
					'nomodule' => true,
				)
			)
		);
	}


	function test_sanitize_script_attributes_no_attributes() {
		add_theme_support( 'html5', array( 'script' ) );

		$this->assertSame(
			'',
			wp_sanitize_script_attributes()
		);

		remove_theme_support( 'html5' );
	}

	function test_sanitize_script_attributes_relative_src() {
		add_theme_support( 'html5', array( 'script' ) );

		$this->assertSame(
			' src="PATH/FILE.js" nomodule',
			wp_sanitize_script_attributes(
				array(
					'src'      => 'PATH/FILE.js',
					'async'    => false,
					'nomodule' => true,
				)
			)
		);

		remove_theme_support( 'html5' );
	}


	function test_sanitize_script_attributes_only_false_boolean_attributes() {
		add_theme_support( 'html5', array( 'script' ) );

		$this->assertSame(
			'',
			wp_sanitize_script_attributes(
				array(
					'async'    => false,
					'nomodule' => false,
				)
			)
		);

		remove_theme_support( 'html5' );
	}

	function test_sanitize_script_attributes_only_true_boolean_attributes() {
		add_theme_support( 'html5', array( 'script' ) );

		$this->assertSame(
			' async nomodule',
			wp_sanitize_script_attributes(
				array(
					'async'    => true,
					'nomodule' => true,
				)
			)
		);

		remove_theme_support( 'html5' );
	}

	function test_sanitize_script_attributes_wp_script_attributes_filter() {
		add_theme_support( 'html5', array( 'script' ) );

		add_filter(
			'wp_script_attributes',
			function( $attributes ) {
				if ( isset( $attributes['id'] ) && 'utils-js-extra' === $attributes['id'] ) {
					$attributes['async'] = true;
				}
				return $attributes;
			}
		);

		$this->assertSame(
			' src="https://DOMAIN.TLD/PATH/FILE.js" id="utils-js-extra" async nomodule',
			wp_sanitize_script_attributes(
				array(
					'src'      => 'https://DOMAIN.TLD/PATH/FILE.js',
					'id'       => 'utils-js-extra',
					'async'    => false,
					'nomodule' => true,
				)
			)
		);

		remove_theme_support( 'html5' );
	}
}
