<?php
/**
 * Test wp_get_font_dir().
 *
 * @package WordPress
 * @subpackage Font Library
 *
 * @group fonts
 * @group font-library
 *
 * @covers ::wp_get_font_dir
 */
class Tests_Fonts_WpFontDir extends WP_UnitTestCase {
	private static $dir_defaults;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		static::$dir_defaults = array(
			'path'    => path_join( WP_CONTENT_DIR, 'fonts' ),
			'url'     => content_url( 'fonts' ),
			'subdir'  => '',
			'basedir' => path_join( WP_CONTENT_DIR, 'fonts' ),
			'baseurl' => content_url( 'fonts' ),
			'error'   => false,
		);
	}

	public function tear_down() {
		$directories = array(
			path_join( WP_CONTENT_DIR, 'fonts' ),
			path_join( WP_CONTENT_DIR, 'uploads/fonts' ),
			'/custom-path/fonts/my-custom-subdir',
		);

		foreach ( $directories as $dir ) {
			if ( ! is_dir( $dir ) ) {
				continue;
			}

			chmod( $dir, 0755 );
			$this->delete_folders( $dir );
		}

		parent::tear_down();
	}

	public function test_fonts_dir() {
		$font_dir = wp_get_font_dir();

		$this->assertSame( $font_dir, static::$dir_defaults );
	}

	public function test_fonts_dir_with_filter() {
		// Define a callback function to pass to the filter.
		function set_new_values( $defaults ) {
			$defaults['path']    = '/custom-path/fonts/my-custom-subdir';
			$defaults['url']     = 'http://example.com/custom-path/fonts/my-custom-subdir';
			$defaults['subdir']  = 'my-custom-subdir';
			$defaults['basedir'] = '/custom-path/fonts';
			$defaults['baseurl'] = 'http://example.com/custom-path/fonts';
			$defaults['error']   = false;
			return $defaults;
		}

		// Add the filter.
		add_filter( 'font_dir', 'set_new_values' );

		// Gets the fonts dir.
		$font_dir = wp_get_font_dir();

		$expected = array(
			'path'    => '/custom-path/fonts/my-custom-subdir',
			'url'     => 'http://example.com/custom-path/fonts/my-custom-subdir',
			'subdir'  => 'my-custom-subdir',
			'basedir' => '/custom-path/fonts',
			'baseurl' => 'http://example.com/custom-path/fonts',
			'error'   => false,
		);

		// Remove the filter.
		remove_filter( 'font_dir', 'set_new_values' );

		$this->assertSame( $expected, $font_dir, 'The wp_get_font_dir() method should return the expected values.' );

		// Gets the fonts dir.
		$font_dir = wp_get_font_dir();

		$this->assertSame( static::$dir_defaults, $font_dir, 'The wp_get_font_dir() method should return the default values.' );
	}

	public function test_fonts_dir_with_uploads_default() {
		$font_dir_before = wp_get_font_dir();

		$this->assertDirectoryExists( $font_dir_before['path'] );

		chmod( $font_dir_before['path'], 0000 );
		$font_dir_after = wp_get_font_dir();

		$upload_dir = wp_upload_dir();

		$this->assertSame( $font_dir_before, static::$dir_defaults );
		$this->assertSame(
			array(
				'path'    => path_join( $upload_dir['basedir'], 'fonts' ),
				'url'     => $upload_dir['baseurl'] . '/fonts',
				'subdir'  => '',
				'basedir' => path_join( $upload_dir['basedir'], 'fonts' ),
				'baseurl' => $upload_dir['baseurl'] . '/fonts',
				'error'   => false,
			),
			$font_dir_after,
			'The font directory should be a subdir in the uploads directory.'
		);
	}
}
