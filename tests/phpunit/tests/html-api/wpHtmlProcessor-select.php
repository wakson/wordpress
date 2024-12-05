<?php
/**
 * Unit tests covering WP_HTML_Processor select functionality.
 *
 * Covers functionality related to CSS selectors and the {@see WP_HTML_Processor::select()}
 * and {@see WP_HTML_Processor::select_all()} methods.
 *
 * @package WordPress
 * @subpackage HTML-API
 *
 * @since TBD
 *
 * @group html-api
 */
class Tests_HtmlApi_WpHtmlProcessor_Select extends WP_UnitTestCase {
	/**
	 * @ticket 62653
	 */
	public function test_select_miss() {
		$processor = WP_HTML_Processor::create_full_parser( '<span>' );
		$this->assertFalse( $processor->select( 'div' ) );
	}

	/**
	 * @ticket 62653
	 *
	 * @dataProvider data_selectors
	 */
	public function test_select_all( string $html, string $selector, int $match_count ) {
		$processor = WP_HTML_Processor::create_full_parser( $html );
		$count     = 0;
		foreach ( $processor->select_all( $selector ) as $_ ) {
			$breadcrumb_string = implode( ', ', $processor->get_breadcrumbs() );
			$this->assertTrue(
				$processor->get_attribute( 'match' ),
				"Matched unexpected tag {$processor->get_tag()} @ {$breadcrumb_string}"
			);
			++$count;
		}
		$this->assertSame( $match_count, $count, 'Did not match expected number of tags.' );
	}

	/**
	 * Data provider.
	 *
	 * Most selectors are covered by the tag processor selector tests.
	 * This suite should focus on complex selectors.
	 *
	 * @return array
	 */
	public static function data_selectors(): array {
		return array(
			'any descendant' => array( '<section><p match><i match><em match><p match>', 'section *', 4 ),
			'any child 1'    => array( '<section><p match><i><em><p match>', 'section > *', 2 ),
			'any child 2'    => array( '<div><section match><div>', 'div > *', 1 ),
		);
	}

	/**
	 * @ticket 62653
	 *
	 * @expectedIncorrectUsage WP_HTML_Processor::select_all
	 *
	 * @dataProvider data_invalid_selectors
	 */
	public function test_invalid_selector( string $selector ) {
		$processor = WP_HTML_Processor::create_fragment( 'irrelevant' );
		$this->assertFalse( $processor->select( $selector ) );
	}

	/**
	 * Data provider.
	 *
	 * @return array
	 */
	public static function data_invalid_selectors(): array {
		return array(
			'invalid selector'                => array( '[invalid!selector]' ),

			// The class selectors below are not allowed in non-final position.
			'unsupported child selector'      => array( '.parent > .child' ),
			'unsupported descendant selector' => array( '.ancestor .descendant' ),
		);
	}
}
