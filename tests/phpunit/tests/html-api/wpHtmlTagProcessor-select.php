<?php
/**
 * Unit tests covering WP_HTML_Tag_Processor CSS selection functionality.
 *
 * Covers functionality related to CSS selectors and the {@see WP_HTML_Tag_Processor::select()}
 * and {@see WP_HTML_Tag_Processor::select_all()} methods.
 *
 * @package WordPress
 * @subpackage HTML-API
 *
 * @since TBD
 *
 * @group html-api
 */
class Tests_HtmlApi_WpHtmlTagProcessor_Select extends WP_UnitTestCase {
	/**
	 * @ticket TBD
	 */
	public function test_select_miss() {
		$processor = new WP_HTML_Tag_Processor( '<span>' );
		$this->assertFalse( $processor->select( 'div' ) );
	}

	/**
	 * @ticket TBD
	 *
	 * @dataProvider data_selectors
	 */
	public function test_select( string $html, string $selector ) {
		$processor = new WP_HTML_Tag_Processor( $html );
		$this->assertTrue( $processor->select( $selector ) );
		$this->assertTrue( $processor->get_attribute( 'match' ) );
	}

	/**
	 * Data provider.
	 *
	 * @return array
	 */
	public static function data_selectors(): array {
		return array(
			'simple type'            => array( '<div match>', 'div' ),
			'any type'               => array( '<html match>', '*' ),
			'simple class'           => array( '<div class="x" match>', '.x' ),
			'simple id'              => array( '<div id="x" match>', '#x' ),
			'simple attribute'       => array( '<div att match>', '[att]' ),
			'attribute value'        => array( '<div att="val" match>', '[att=val]' ),
			'attribute quoted value' => array( '<div att="::" match>', '[att="::"]' ),

			'list'                   => array( '<div><p match>', 'a, p' ),
			'compound'               => array( '<div att><section att="foo bar" match>', 'section[att~="bar"]' ),
		);
	}

	/**
	 * @ticket TBD
	 */
	public function test_select_all() {
		$processor = new WP_HTML_Tag_Processor( '<div match><p class="x" match><svg><rect match/></svg><i id="y" match></i>' );
		$count     = 0;
		foreach ( $processor->select_all( 'div, .x, rect, #y' ) as $_ ) {
			++$count;
			$this->assertTrue( $processor->get_attribute( 'match' ) );
		}
		$this->assertSame( 4, $count );
	}

	/**
	 * @ticket TBD
	 *
	 * @expectedIncorrectUsage WP_HTML_Tag_Processor::select_all
	 *
	 * @dataProvider data_invalid_selectors
	 */
	public function test_invalid_selector( string $selector ) {
		$processor = new WP_HTML_Tag_Processor( 'irrelevant' );
		$this->assertFalse( $processor->select( $selector ) );
	}

	/**
	 * Data provider.
	 *
	 * @return array
	 */
	public static function data_invalid_selectors(): array {
		return array(
			'complex descendant' => array( 'div *' ),
			'complex child'      => array( 'div > *' ),
			'invalid selector'   => array( '[invalid!selector]' ),
		);
	}
}
