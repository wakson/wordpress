<?php

/**
 * @group block-templates
 * @covers ::get_template_hierarchy
 */
abstract class WP_Block_Templates_UnitTestCase extends WP_UnitTestCase {
	const TEST_THEME = 'block-theme';

	protected static $template_post;
	protected static $template_part_post;
	protected static $uncustomized_template_db_object;
	protected static $customized_template_db_object;


	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		/*
		 * Set up a template post corresponding to a different theme.
		 * Do this to ensure resolution and slug creation works as expected,
		 * even with another post of that same name present for another theme.
		 */
		self::$template_post = $factory->post->create_and_get(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'my_template',
				'post_title'   => 'My Template',
				'post_content' => 'Content',
				'post_excerpt' => 'Description of my template',
				'tax_input'    => array(
					'wp_theme' => array(
						'this-theme-should-not-resolve',
					),
				),
			)
		);

		wp_set_post_terms( self::$template_post->ID, 'this-theme-should-not-resolve', 'wp_theme' );

		// Set up template post.
		self::$template_post = $factory->post->create_and_get(
			array(
				'post_type'    => 'wp_template',
				'post_name'    => 'my_template',
				'post_title'   => 'My Template',
				'post_content' => '<!-- wp:heading {"level":1,"metadata":{"ignoredHookedBlocks":["tests/ignored"]}} --><h1>Template</h1><!-- /wp:heading -->',
				'post_excerpt' => 'Description of my template',
				'tax_input'    => array(
					'wp_theme' => array(
						self::TEST_THEME,
					),
				),
			)
		);

		wp_set_post_terms( self::$template_post->ID, self::TEST_THEME, 'wp_theme' );

		// Set up template part post.
		self::$template_part_post = $factory->post->create_and_get(
			array(
				'post_type'    => 'wp_template_part',
				'post_name'    => 'my_template_part',
				'post_title'   => 'My Template Part',
				'post_content' => '<!-- wp:heading {"level":2,"metadata":{"ignoredHookedBlocks":["tests/ignored"]}} --><h2>Template Part</h2><!-- /wp:heading -->',
				'post_excerpt' => 'Description of my template part',
				'tax_input'    => array(
					'wp_theme'              => array(
						self::TEST_THEME,
					),
					'wp_template_part_area' => array(
						WP_TEMPLATE_PART_AREA_HEADER,
					),
				),
			)
		);

		wp_set_post_terms( self::$template_part_post->ID, WP_TEMPLATE_PART_AREA_HEADER, 'wp_template_part_area' );
		wp_set_post_terms( self::$template_part_post->ID, self::TEST_THEME, 'wp_theme' );

		// Setup uncustomized template db object.
		self::$uncustomized_template_db_object = (object) array(
			'post_type'    => 'wp_template',
			'post_status'  => 'publish',
			'tax_input'    => array(
				'wp_theme' => self::TEST_THEME,
			),
			'meta_input'   => array(
				'origin' => 'theme',
			),
			'post_content' => '<!-- wp:heading {"level":1,"metadata":{"ignoredHookedBlocks":["tests/ignored"]}} --><h1>Template</h1><!-- /wp:heading -->',
			'post_type'    => 'wp_template',
			'post_name'    => 'my_template',
			'post_title'   => 'My Template',
			'post_excerpt' => 'Description of my template',
		);

		// Setup customized template db object.
		self::$customized_template_db_object = (object) array(
			'post_name'    => 'my_template',
			'post_title'   => 'My Customized Template',
			'post_status'  => 'publish',
			'post_content' => '<!-- wp:heading {"level":1,"metadata":{"ignoredHookedBlocks":["tests/ignored"]}} --><h1>Template</h1><!-- /wp:heading -->',
		);
	}

	public static function wpTearDownAfterClass() {
		wp_delete_post( self::$template_post->ID );
		wp_delete_post( self::$template_part_post->ID );
	}

	public function set_up() {
		parent::set_up();
		switch_theme( self::TEST_THEME );
	}
}
