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
class Tests_HtmlApi_WpHtmlProcessorSetHtml extends WP_UnitTestCase {
	/**
	 * @ticket TBD
	 *
	 * @dataProvider data_set_inner_html_not_allowed
	 */
	public function test_set_inner_html_not_allowed( string $html, string $replacement ) {
		$processor = WP_HTML_Processor::create_fragment( $html );
		while ( $processor->next_tag() ) {
			if ( $processor->get_attribute( 'target' ) ) {
				break;
			}
		}
		$this->assertFalse( $processor->set_inner_html( $replacement ), "Should have failed but produced: {$processor->get_updated_html()}" );
		$this->assertSame( $html, $processor->get_updated_html() );
	}

	/**
	 * Data provider.
	 *
	 * @return array[]
	 */
	public static function data_set_inner_html_not_allowed(): array {
		return array(
			'not allowed in void tags'         => array(
				'<br target>',
				'anything',
			),
			'not allowed in self-closing tags' => array(
				'<svg><text target />',
				'anything',
			),
			'must have closing tag'            => array(
				'<body><div target></body>',
				'anything',
			),

			'a in a'                           => array(
				'<a target></a>',
				'<a>',
			),
			'a nested in a'                    => array(
				'<a><i><em><strong target></a>',
				'<a>A cannot nest inside a',
			),

			'text in table'                    => array(
				'<table target><td>hello</table>',
				'text triggers forstering - not allowed',
			),
			'text in thead'                    => array(
				'<table><thead target><td>hello</thead>',
				'text triggers forstering - not allowed',
			),
			'text in tr'                       => array(
				'<table><tr target>hello</tr>',
				'text triggers forstering - not allowed',
			),
		);
	}
}
