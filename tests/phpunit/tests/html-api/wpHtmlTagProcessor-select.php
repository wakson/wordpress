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
			'simple type'                           => array( '<p><div match>', 'div' ),
			'any type'                              => array( '<div match>', '*' ),
			'simple class'                          => array( '<div><div class="x" match>', '.x' ),
			'simple id'                             => array( '<div><div id="x" match>', '#x' ),
			'boolean attribute'                     => array( '<div tta><div att match>', '[att]' ),
			'boolean attribute with string match'   => array( '<div tta><div att match>', '[att=""]' ),

			'attribute value'                       => array( '<div att="v"><div att="val" match>', '[att=val]' ),
			'attribute quoted value'                => array( '<div att><div att="::" match>', '[att="::"]' ),
			'attribute case insensitive'            => array( '<div att="value"><div att="val" match>', '[att="VAL"i]' ),
			'attribute case sensitive mod'          => array( '<div att="VAL"><div att="val" match>', '[att="val"s]' ),

			'attribute one of'                      => array( '<div att="a B c"><div att="a b c" match>', '[att~="b"]' ),
			'attribute one of insensitive'          => array( '<div att="a c"><div att="a B c" match>', '[att~="b"i]' ),
			'attribute one of mod sensitive'        => array( '<div att="a B c"><div att="a b c" match>', '[att~="b"s]' ),
			'attribute one of whitespace cases'     => array( "<div att='     a'><div att='\na\ta   b   ' match>", '[att~="b"]' ),

			'attribute with-hyphen (no hyphen)'     => array( '<p att="special_not"><p att="special" match>', '[att|="special"]' ),
			'attribute with-hyphen (hyphen prefix)' => array( '<p att="special_not"><p att="special-yeah" match>', '[att|="special"]' ),
			'attribute with-hyphen insensitive'     => array( '<p att="special_not"><p att="SPECIAL" match>', '[att|="special"i]' ),
			'attribute with-hyphen sensitive mod'   => array( '<p att="spec"><p att="special" match>', '[att|="special"s]' ),

			'attribute prefixed'                    => array( '<p att="fix"><p att="prefix" match>', '[att^="p"]' ),
			'attribute prefixed insensitive'        => array( '<p att="fix"><p att="Prefix" match>', '[att^="p"i]' ),
			'attribute prefixed sensitive mod'      => array( '<p att="Prefix"><p att="prefix" match>', '[att^="p"s]' ),

			'attribute suffixed'                    => array( '<p att="suffix_"><p att="suffix" match>', '[att$="x"]' ),
			'attribute suffixed insensitive'        => array( '<p att="suffix_"><p att="suffiX" match>', '[att$="x"i]' ),
			'attribute suffixed sensitive mod'      => array( '<p att="suffiX"><p att="suffix" match>', '[att$="x"s]' ),

			'attribute contains'                    => array( '<p att="abcyz"><p att="abcxyz" match>', '[att*="x"]' ),
			'attribute contains insensitive'        => array( '<p att="abcyz"><p att="abcXyz" match>', '[att*="x"i]' ),
			'attribute contains sensitive mod'      => array( '<p att="abcXyz"><p att="abcxyz" match>', '[att*="x"s]' ),

			'list'                                  => array( '<div><p match>', 'a, p' ),
			'compound'                              => array( '<div att><section att="bar" match>', 'section[att="bar"]' ),
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
