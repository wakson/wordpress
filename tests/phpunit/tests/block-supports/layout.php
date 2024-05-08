<?php
/**
 * Tests for block supports related to layout.
 *
 * @package WordPress
 * @subpackage Block Supports
 * @since 6.0.0
 *
 * @group block-supports
 *
 * @covers ::wp_restore_image_outer_container
 */
class Test_Block_Supports_Layout extends WP_UnitTestCase {

	/**
	 * Theme root directory.
	 *
	 * @var string
	 */
	private $theme_root;

	/**
	 * Original theme directory.
	 *
	 * @var string
	 */
	private $orig_theme_dir;

	public function set_up() {
		parent::set_up();
		$this->theme_root     = realpath( DIR_TESTDATA . '/themedir1' );
		$this->orig_theme_dir = $GLOBALS['wp_theme_directories'];

		// /themes is necessary as theme.php functions assume /themes is the root if there is only one root.
		$GLOBALS['wp_theme_directories'] = array( WP_CONTENT_DIR . '/themes', $this->theme_root );

		// Set up the new root.
		add_filter( 'theme_root', array( $this, 'filter_set_theme_root' ) );
		add_filter( 'stylesheet_root', array( $this, 'filter_set_theme_root' ) );
		add_filter( 'template_root', array( $this, 'filter_set_theme_root' ) );

		// Clear caches.
		wp_clean_themes_cache();
		unset( $GLOBALS['wp_themes'] );
	}

	public function tear_down() {
		$GLOBALS['wp_theme_directories'] = $this->orig_theme_dir;

		// Clear up the filters to modify the theme root.
		remove_filter( 'theme_root', array( $this, 'filter_set_theme_root' ) );
		remove_filter( 'stylesheet_root', array( $this, 'filter_set_theme_root' ) );
		remove_filter( 'template_root', array( $this, 'filter_set_theme_root' ) );

		wp_clean_themes_cache();
		unset( $GLOBALS['wp_themes'] );
		parent::tear_down();
	}

	public function filter_set_theme_root() {
		return $this->theme_root;
	}

	/**
	 * @ticket 55505
	 */
	public function test_outer_container_not_restored_for_non_aligned_image_block_with_non_themejson_theme() {
		// The "default" theme doesn't have theme.json support.
		switch_theme( 'default' );
		$block         = array(
			'blockName' => 'core/image',
			'attrs'     => array(),
		);
		$block_content = '<figure class="wp-block-image size-full"><img src="/my-image.jpg"/></figure>';
		$expected      = '<figure class="wp-block-image size-full"><img src="/my-image.jpg"/></figure>';

		$this->assertSame( $expected, wp_restore_image_outer_container( $block_content, $block ) );
	}

	/**
	 * @ticket 55505
	 */
	public function test_outer_container_restored_for_aligned_image_block_with_non_themejson_theme() {
		// The "default" theme doesn't have theme.json support.
		switch_theme( 'default' );
		$block         = array(
			'blockName' => 'core/image',
			'attrs'     => array(),
		);
		$block_content = '<figure class="wp-block-image alignright size-full"><img src="/my-image.jpg"/></figure>';
		$expected      = '<div class="wp-block-image"><figure class="alignright size-full"><img src="/my-image.jpg"/></figure></div>';

		$this->assertSame( $expected, wp_restore_image_outer_container( $block_content, $block ) );
	}

	/**
	 * @ticket 55505
	 *
	 * @dataProvider data_block_image_html_restored_outer_container
	 *
	 * @param string $block_image_html The block image HTML passed to `wp_restore_image_outer_container`.
	 * @param string $expected         The expected block image HTML.
	 */
	public function test_additional_styles_moved_to_restored_outer_container_for_aligned_image_block_with_non_themejson_theme( $block_image_html, $expected ) {
		// The "default" theme doesn't have theme.json support.
		switch_theme( 'default' );
		$block = array(
			'blockName' => 'core/image',
			'attrs'     => array(
				'className' => 'is-style-round my-custom-classname',
			),
		);

		$this->assertSame( $expected, wp_restore_image_outer_container( $block_image_html, $block ) );
	}

	/**
	 * Data provider for test_additional_styles_moved_to_restored_outer_container_for_aligned_image_block_with_non_themejson_theme().
	 *
	 * @return array {
	 *     @type array {
	 *         @type string $block_image_html The block image HTML passed to `wp_restore_image_outer_container`.
	 *         @type string $expected         The expected block image HTML.
	 *     }
	 * }
	 */
	public function data_block_image_html_restored_outer_container() {
		$expected = '<div class="wp-block-image is-style-round my-custom-classname"><figure class="alignright size-full"><img src="/my-image.jpg"/></figure></div>';

		return array(
			array(
				'<figure class="wp-block-image alignright size-full is-style-round my-custom-classname"><img src="/my-image.jpg"/></figure>',
				$expected,
			),
			array(
				'<figure class="is-style-round my-custom-classname wp-block-image alignright size-full"><img src="/my-image.jpg"/></figure>',
				$expected,
			),
			array(
				'<figure class="wp-block-image is-style-round my-custom-classname alignright size-full"><img src="/my-image.jpg"/></figure>',
				$expected,
			),
			array(
				'<figure class="is-style-round wp-block-image alignright my-custom-classname size-full"><img src="/my-image.jpg"/></figure>',
				$expected,
			),
			array(
				'<figure style="color: red" class=\'is-style-round wp-block-image alignright my-custom-classname size-full\' data-random-tag=">"><img src="/my-image.jpg"/></figure>',
				'<div class="wp-block-image is-style-round my-custom-classname"><figure style="color: red" class=\'alignright size-full\' data-random-tag=">"><img src="/my-image.jpg"/></figure></div>',
			),
		);
	}

	/**
	 * @ticket 55505
	 */
	public function test_outer_container_not_restored_for_aligned_image_block_with_themejson_theme() {
		switch_theme( 'block-theme' );
		$block         = array(
			'blockName' => 'core/image',
			'attrs'     => array(
				'className' => 'is-style-round my-custom-classname',
			),
		);
		$block_content = '<figure class="wp-block-image alignright size-full is-style-round my-custom-classname"><img src="/my-image.jpg"/></figure>';
		$expected      = '<figure class="wp-block-image alignright size-full is-style-round my-custom-classname"><img src="/my-image.jpg"/></figure>';

		$this->assertSame( $expected, wp_restore_image_outer_container( $block_content, $block ) );
	}

	/**
	 * @ticket 57584
	 * @ticket 58548
	 * @ticket 60292
	 *
	 * @dataProvider data_layout_support_flag_renders_classnames_on_wrapper
	 *
	 * @covers ::wp_render_layout_support_flag
	 *
	 * @param array  $args            Dataset to test.
	 * @param string $expected_output The expected output.
	 */
	public function test_layout_support_flag_renders_classnames_on_wrapper( $args, $expected_output ) {
		$actual_output = wp_render_layout_support_flag( $args['block_content'], $args['block'] );
		$this->assertSame( $expected_output, $actual_output );
	}

	/**
	 * Data provider for test_layout_support_flag_renders_classnames_on_wrapper.
	 *
	 * @return array
	 */
	public function data_layout_support_flag_renders_classnames_on_wrapper() {
		return array(
			'single wrapper block layout with flow type'   => array(
				'args'            => array(
					'block_content' => '<div class="wp-block-group"></div>',
					'block'         => array(
						'blockName'    => 'core/group',
						'attrs'        => array(
							'layout' => array(
								'type' => 'default',
							),
						),
						'innerBlocks'  => array(),
						'innerHTML'    => '<div class="wp-block-group"></div>',
						'innerContent' => array(
							'<div class="wp-block-group"></div>',
						),
					),
				),
				'expected_output' => '<div class="wp-block-group is-layout-flow wp-block-group-is-layout-flow"></div>',
			),
			'single wrapper block layout with constrained type' => array(
				'args'            => array(
					'block_content' => '<div class="wp-block-group"></div>',
					'block'         => array(
						'blockName'    => 'core/group',
						'attrs'        => array(
							'layout' => array(
								'type' => 'constrained',
							),
						),
						'innerBlocks'  => array(),
						'innerHTML'    => '<div class="wp-block-group"></div>',
						'innerContent' => array(
							'<div class="wp-block-group"></div>',
						),
					),
				),
				'expected_output' => '<div class="wp-block-group is-layout-constrained wp-block-group-is-layout-constrained"></div>',
			),
			'multiple wrapper block layout with flow type' => array(
				'args'            => array(
					'block_content' => '<div class="wp-block-group"><div class="wp-block-group__inner-wrapper"></div></div>',
					'block'         => array(
						'blockName'    => 'core/group',
						'attrs'        => array(
							'layout' => array(
								'type' => 'default',
							),
						),
						'innerBlocks'  => array(),
						'innerHTML'    => '<div class="wp-block-group"><div class="wp-block-group__inner-wrapper"></div></div>',
						'innerContent' => array(
							'<div class="wp-block-group"><div class="wp-block-group__inner-wrapper">',
							' ',
							' </div></div>',
						),
					),
				),
				'expected_output' => '<div class="wp-block-group"><div class="wp-block-group__inner-wrapper is-layout-flow wp-block-group-is-layout-flow"></div></div>',
			),
			'skip classname output if block does not support layout and there are no child layout classes to be output' => array(
				'args'            => array(
					'block_content' => '<p>A paragraph</p>',
					'block'         => array(
						'blockName'    => 'core/paragraph',
						'attrs'        => array(
							'style' => array(
								'layout' => array(
									'selfStretch' => 'fit',
								),
							),
						),
						'innerBlocks'  => array(),
						'innerHTML'    => '<p>A paragraph</p>',
						'innerContent' => array( '<p>A paragraph</p>' ),
					),
				),
				'expected_output' => '<p>A paragraph</p>',
			),
		);
	}

	/**
	 * Check that wp_restore_group_inner_container() restores the legacy inner container on the Group block.
	 *
	 * @ticket 60130
	 *
	 * @covers ::wp_restore_group_inner_container
	 *
	 * @dataProvider data_restore_group_inner_container
	 *
	 * @param array  $args            Dataset to test.
	 * @param string $expected_output The expected output.
	 */
	public function test_restore_group_inner_container( $args, $expected_output ) {
		$actual_output = wp_restore_group_inner_container( $args['block_content'], $args['block'] );
		$this->assertEquals( $expected_output, $actual_output );
	}

	/**
	 * Data provider for test_restore_group_inner_container.
	 *
	 * @return array
	 */
	public function data_restore_group_inner_container() {
		return array(
			'group block with existing inner container'    => array(
				'args'            => array(
					'block_content' => '<div class="wp-block-group"><div class="wp-block-group__inner-container"></div></div>',
					'block'         => array(
						'blockName'    => 'core/group',
						'attrs'        => array(
							'layout' => array(
								'type' => 'default',
							),
						),
						'innerBlocks'  => array(),
						'innerHTML'    => '<div class="wp-block-group"><div class="wp-block-group__inner-container"></div></div>',
						'innerContent' => array(
							'<div class="wp-block-group"><div class="wp-block-group__inner-container">',
							' ',
							' </div></div>',
						),
					),
				),
				'expected_output' => '<div class="wp-block-group"><div class="wp-block-group__inner-container"></div></div>',
			),
			'group block with no existing inner container' => array(
				'args'            => array(
					'block_content' => '<div class="wp-block-group"></div>',
					'block'         => array(
						'blockName'    => 'core/group',
						'attrs'        => array(
							'layout' => array(
								'type' => 'default',
							),
						),
						'innerBlocks'  => array(),
						'innerHTML'    => '<div class="wp-block-group"></div>',
						'innerContent' => array(
							'<div class="wp-block-group">',
							' ',
							' </div>',
						),
					),
				),
				'expected_output' => '<div class="wp-block-group"><div class="wp-block-group__inner-container"></div></div>',
			),
			'group block with layout classnames'           => array(
				'args'            => array(
					'block_content' => '<div class="wp-block-group is-layout-constrained wp-block-group-is-layout-constrained"></div>',
					'block'         => array(
						'blockName'    => 'core/group',
						'attrs'        => array(
							'layout' => array(
								'type' => 'default',
							),
						),
						'innerBlocks'  => array(),
						'innerHTML'    => '<div class="wp-block-group"></div>',
						'innerContent' => array(
							'<div class="wp-block-group">',
							' ',
							' </div>',
						),
					),
				),
				'expected_output' => '<div class="wp-block-group"><div class="wp-block-group__inner-container is-layout-constrained wp-block-group-is-layout-constrained"></div></div>',
			),
		);
	}

	const ARGS_DEFAULTS = array(
		'selector'                      => null,
		'layout'                        => null,
		'has_block_gap_support'         => false,
		'gap_value'                     => null,
		'should_skip_gap_serialization' => false,
		'fallback_gap_value'            => '0.5em',
		'block_spacing'                 => null,
	);

	/**
	 * Generates the CSS corresponding to the provided layout.
	 *
	 * @ticket 61165
	 *
	 * @dataProvider data_wp_get_layout_style
	 *
	 * @covers ::wp_get_layout_style
	 *
	 * @param array  $args            Dataset to test.
	 * @param string $expected_output The expected output.
	 */
	public function test_wp_get_layout_style( $args, $expected_output ) {
		$args          = array_merge( static::ARGS_DEFAULTS, $args );
		$layout_styles = wp_get_layout_style(
			$args['selector'],
			$args['layout'],
			$args['has_block_gap_support'],
			$args['gap_value'],
			$args['should_skip_gap_serialization'],
			$args['fallback_gap_value'],
			$args['block_spacing']
		);

		$this->assertSame( $expected_output, $layout_styles );
	}

	/**
	 * Data provider for test_wp_get_layout_style().
	 *
	 * @return array
	 */
	public function data_wp_get_layout_style() {
		return array(
			'no args should return empty value'            => array(
				'args'            => array(),
				'expected_output' => '',
			),
			'nulled args should return empty value'        => array(
				'args'            => array(
					'selector'                      => null,
					'layout'                        => null,
					'has_block_gap_support'         => null,
					'gap_value'                     => null,
					'should_skip_gap_serialization' => null,
					'fallback_gap_value'            => null,
					'block_spacing'                 => null,
				),
				'expected_output' => '',
			),
			'only selector should return empty value'      => array(
				'args'            => array(
					'selector' => '.wp-layout',
				),
				'expected_output' => '',
			),
			'default layout and block gap support'         => array(
				'args'            => array(
					'selector'              => '.wp-layout',
					'has_block_gap_support' => true,
					'gap_value'             => '1em',
				),
				'expected_output' => '.wp-layout > *{margin-block-start:0;margin-block-end:0;}.wp-layout > * + *{margin-block-start:1em;margin-block-end:0;}',
			),
			'skip serialization should return empty value' => array(
				'args'            => array(
					'selector'                      => '.wp-layout',
					'has_block_gap_support'         => true,
					'gap_value'                     => '1em',
					'should_skip_gap_serialization' => true,
				),
				'expected_output' => '',
			),
			'default layout and axial block gap support'   => array(
				'args'            => array(
					'selector'              => '.wp-layout',
					'has_block_gap_support' => true,
					'gap_value'             => array( 'top' => '1em' ),
				),
				'expected_output' => '.wp-layout > *{margin-block-start:0;margin-block-end:0;}.wp-layout > * + *{margin-block-start:1em;margin-block-end:0;}',
			),
			'constrained layout with sizes'                => array(
				'args'            => array(
					'selector' => '.wp-layout',
					'layout'   => array(
						'type'        => 'constrained',
						'contentSize' => '800px',
						'wideSize'    => '1200px',
					),
				),
				'expected_output' => '.wp-layout > :where(:not(.alignleft):not(.alignright):not(.alignfull)){max-width:800px;margin-left:auto !important;margin-right:auto !important;}.wp-layout > .alignwide{max-width:1200px;}.wp-layout .alignfull{max-width:none;}',
			),
			'constrained layout with sizes and block spacing' => array(
				'args'            => array(
					'selector'      => '.wp-layout',
					'layout'        => array(
						'type'        => 'constrained',
						'contentSize' => '800px',
						'wideSize'    => '1200px',
					),
					'block_spacing' => array(
						'padding' => array(
							'left'  => '20px',
							'right' => '10px',
						),
					),
				),
				'expected_output' => '.wp-layout > :where(:not(.alignleft):not(.alignright):not(.alignfull)){max-width:800px;margin-left:auto !important;margin-right:auto !important;}.wp-layout > .alignwide{max-width:1200px;}.wp-layout .alignfull{max-width:none;}.wp-layout > .alignfull{margin-right:calc(10px * -1);margin-left:calc(20px * -1);}',
			),
			'constrained layout with block gap support'    => array(
				'args'            => array(
					'selector'              => '.wp-layout',
					'layout'                => array(
						'type' => 'constrained',
					),
					'has_block_gap_support' => true,
					'gap_value'             => '2.5rem',
				),
				'expected_output' => '.wp-layout > *{margin-block-start:0;margin-block-end:0;}.wp-layout > * + *{margin-block-start:2.5rem;margin-block-end:0;}',
			),
			'constrained layout with axial block gap support' => array(
				'args'            => array(
					'selector'              => '.wp-layout',
					'layout'                => array(
						'type' => 'constrained',
					),
					'has_block_gap_support' => true,
					'gap_value'             => array( 'top' => '2.5rem' ),
				),
				'expected_output' => '.wp-layout > *{margin-block-start:0;margin-block-end:0;}.wp-layout > * + *{margin-block-start:2.5rem;margin-block-end:0;}',
			),
			'constrained layout with block gap support and spacing preset' => array(
				'args'            => array(
					'selector'              => '.wp-layout',
					'layout'                => array(
						'type' => 'constrained',
					),
					'has_block_gap_support' => true,
					'gap_value'             => 'var:preset|spacing|50',
				),
				'expected_output' => '.wp-layout > *{margin-block-start:0;margin-block-end:0;}.wp-layout > * + *{margin-block-start:var(--wp--preset--spacing--50);margin-block-end:0;}',
			),
			'flex layout with no args should return empty value' => array(
				'args'            => array(
					'selector' => '.wp-layout',
					'layout'   => array(
						'type' => 'flex',
					),
				),
				'expected_output' => '',
			),
			'horizontal flex layout should return empty value' => array(
				'args'            => array(
					'selector' => '.wp-layout',
					'layout'   => array(
						'type'        => 'flex',
						'orientation' => 'horizontal',
					),
				),
				'expected_output' => '',
			),
			'flex layout with properties'                  => array(
				'args'            => array(
					'selector' => '.wp-layout',
					'layout'   => array(
						'type'              => 'flex',
						'orientation'       => 'horizontal',
						'flexWrap'          => 'nowrap',
						'justifyContent'    => 'left',
						'verticalAlignment' => 'bottom',
					),
				),
				'expected_output' => '.wp-layout{flex-wrap:nowrap;justify-content:flex-start;align-items:flex-end;}',
			),
			'flex layout with properties and block gap'    => array(
				'args'            => array(
					'selector'              => '.wp-layout',
					'layout'                => array(
						'type'              => 'flex',
						'orientation'       => 'horizontal',
						'flexWrap'          => 'nowrap',
						'justifyContent'    => 'left',
						'verticalAlignment' => 'bottom',
					),
					'has_block_gap_support' => true,
					'gap_value'             => '29px',
				),
				'expected_output' => '.wp-layout{flex-wrap:nowrap;gap:29px;justify-content:flex-start;align-items:flex-end;}',
			),
			'flex layout with properties and axial block gap' => array(
				'args'            => array(
					'selector'              => '.wp-layout',
					'layout'                => array(
						'type'              => 'flex',
						'orientation'       => 'horizontal',
						'flexWrap'          => 'nowrap',
						'justifyContent'    => 'left',
						'verticalAlignment' => 'bottom',
					),
					'has_block_gap_support' => true,
					'gap_value'             => array(
						'top'  => '1px',
						'left' => '2px',
					),
				),
				'expected_output' => '.wp-layout{flex-wrap:nowrap;gap:1px 2px;justify-content:flex-start;align-items:flex-end;}',
			),
			'flex layout with properties and axial block gap using spacing preset' => array(
				'args'            => array(
					'selector'              => '.wp-layout',
					'layout'                => array(
						'type'              => 'flex',
						'orientation'       => 'horizontal',
						'flexWrap'          => 'nowrap',
						'justifyContent'    => 'left',
						'verticalAlignment' => 'bottom',
					),
					'has_block_gap_support' => true,
					'gap_value'             => array(
						'left' => 'var:preset|spacing|40',
					),
					'fallback_gap_value'    => '11px',
				),
				'expected_output' => '.wp-layout{flex-wrap:nowrap;gap:11px var(--wp--preset--spacing--40);justify-content:flex-start;align-items:flex-end;}',
			),
			'vertical flex layout with properties'         => array(
				'args'            => array(
					'selector' => '.wp-layout',
					'layout'   => array(
						'type'              => 'flex',
						'orientation'       => 'vertical',
						'flexWrap'          => 'nowrap',
						'justifyContent'    => 'left',
						'verticalAlignment' => 'bottom',
					),
				),
				'expected_output' => '.wp-layout{flex-wrap:nowrap;flex-direction:column;align-items:flex-start;justify-content:flex-end;}',
			),
			'default grid layout'                          => array(
				'args'            => array(
					'selector' => '.wp-layout',
					'layout'   => array(
						'type' => 'grid',
					),
				),
				'expected_output' => '.wp-layout{grid-template-columns:repeat(auto-fill, minmax(min(12rem, 100%), 1fr));}',
			),
			'grid layout with columnCount'                 => array(
				'args'            => array(
					'selector' => '.wp-layout',
					'layout'   => array(
						'type'        => 'grid',
						'columnCount' => 3,
					),
				),
				'expected_output' => '.wp-layout{grid-template-columns:repeat(3, minmax(0, 1fr));}',
			),
			'default layout with blockGap to verify converting gap value into valid CSS' => array(
				'args'            => array(
					'selector'              => '.wp-block-group.wp-container-6',
					'layout'                => array(
						'type' => 'default',
					),
					'has_block_gap_support' => true,
					'gap_value'             => 'var:preset|spacing|70',
					'block_spacing'         => array(
						'blockGap' => 'var(--wp--preset--spacing--70)',
					),
				),
				'expected_output' => '.wp-block-group.wp-container-6 > *{margin-block-start:0;margin-block-end:0;}.wp-block-group.wp-container-6 > * + *{margin-block-start:var(--wp--preset--spacing--70);margin-block-end:0;}',
			),
		);
	}
}
