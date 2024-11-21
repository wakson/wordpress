<?php
/**
 * Unit tests covering WP_HTML_Processor fragment parsing functionality.
 *
 * @package WordPress
 * @subpackage HTML-API
 *
 * @since 6.8.0
 *
 * @group html-api
 *
 * @coversDefaultClass WP_HTML_Processor
 */
class Tests_HtmlApi_WpHtmlProcessorFragmentParsing extends WP_UnitTestCase {
	/**
	 * Verifies that the fragment parser doesn't allow invalid context nodes.
	 *
	 * This includes void elements and self-contained elements because they can
	 * contain no inner HTML. Operations on self-contained elements should occur
	 * through methods such as {@see WP_HTML_Tag_Processor::set_modifiable_text}.
	 *
	 * @ticket 62357
	 *
	 * @dataProvider data_invalid_fragment_contexts
	 *
	 * @param string $context Invalid context node for fragment parser.
	 */
	public function test_rejects_invalid_fragment_contexts( string $context, string $doing_it_wrong_method_name ) {
		$this->setExpectedIncorrectUsage( "WP_HTML_Processor::{$doing_it_wrong_method_name}" );
		$this->assertNull(
			WP_HTML_Processor::create_fragment( 'just a test', $context ),
			"Should not have been able to create a fragment parser with context node {$context}"
		);
	}

	/**
	 * Data provider.
	 *
	 * @ticket 62357
	 *
	 * @return array[]
	 */
	public static function data_invalid_fragment_contexts() {
		return array(
			/*
			 * Invalid contexts.
			 */
			/*
			 * The text node is confused with a virtual body open tag.
			 * This should fail to set a bookmark in `create_fragment`
			 * but currently does not, it slips through and fails in
			 * `create_fragment_at_current_node`.
			 */
			'Invalid text'          => array( 'just some text', 'create_fragment_at_current_node' ),
			'Invalid comment'       => array( '<!-- comment -->', 'create_fragment' ),
			'Invalid closing'       => array( '</div>', 'create_fragment' ),
			'Invalid DOCTYPE'       => array( '<!DOCTYPE html>', 'create_fragment' ),
			/*
			 * PLAINTEXT should appear in the unsupported elements, but at the
			 * moment it's completely unsupported by the processor so
			 * the context element cannot be found.
			 */
			'Unsupported PLAINTEXT' => array( '<plaintext>', 'create_fragment' ),

			/*
			 * Invalid contexts.
			 */
			'AREA'                  => array( '<area>', 'create_fragment_at_current_node' ),
			'BASE'                  => array( '<base>', 'create_fragment_at_current_node' ),
			'BASEFONT'              => array( '<basefont>', 'create_fragment_at_current_node' ),
			'BGSOUND'               => array( '<bgsound>', 'create_fragment_at_current_node' ),
			'BR'                    => array( '<br>', 'create_fragment_at_current_node' ),
			'COL'                   => array( '<table><colgroup><col>', 'create_fragment_at_current_node' ),
			'EMBED'                 => array( '<embed>', 'create_fragment_at_current_node' ),
			'FRAME'                 => array( '<frameset><frame>', 'create_fragment_at_current_node' ),
			'HR'                    => array( '<hr>', 'create_fragment_at_current_node' ),
			'IMG'                   => array( '<img>', 'create_fragment_at_current_node' ),
			'INPUT'                 => array( '<input>', 'create_fragment_at_current_node' ),
			'KEYGEN'                => array( '<keygen>', 'create_fragment_at_current_node' ),
			'LINK'                  => array( '<link>', 'create_fragment_at_current_node' ),
			'META'                  => array( '<meta>', 'create_fragment_at_current_node' ),
			'PARAM'                 => array( '<param>', 'create_fragment_at_current_node' ),
			'SOURCE'                => array( '<source>', 'create_fragment_at_current_node' ),
			'TRACK'                 => array( '<track>', 'create_fragment_at_current_node' ),
			'WBR'                   => array( '<wbr>', 'create_fragment_at_current_node' ),

			/*
			 * Unsupported elements. Include a tag closer to ensure the element can be found
			 * and does not pause the parser at an incomplete token.
			 */
			'IFRAME'                => array( '<iframe></iframe>', 'create_fragment_at_current_node' ),
			'NOEMBED'               => array( '<noembed></noembed>', 'create_fragment_at_current_node' ),
			'NOFRAMES'              => array( '<noframes></noframes>', 'create_fragment_at_current_node' ),
			'SCRIPT'                => array( '<script></script>', 'create_fragment_at_current_node' ),
			'SCRIPT with type'      => array( '<script type="javascript"></script>', 'create_fragment_at_current_node' ),
			'STYLE'                 => array( '<style></style>', 'create_fragment_at_current_node' ),
			'TEXTAREA'              => array( '<textarea></textarea>', 'create_fragment_at_current_node' ),
			'TITLE'                 => array( '<title></title>', 'create_fragment_at_current_node' ),
			'XMP'                   => array( '<xmp></xmp>', 'create_fragment_at_current_node' ),
		);
	}
}
