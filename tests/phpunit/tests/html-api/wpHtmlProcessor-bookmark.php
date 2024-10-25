<?php
/**
 * Unit tests covering WP_HTML_Processor bookmark functionality.
 *
 * @package WordPress
 * @subpackage HTML-API
 */

/**
 * @group html-api
 *
 * @coversDefaultClass WP_HTML_Processor
 */
class Tests_HtmlApi_WpHtmlProcessor_Bookmark extends WP_UnitTestCase {
	/**
	 * Ensures that bookmarks can be set and seeked to for the full processor.
	 *
	 * @ticket 62290
	 */
	public function test_full_processor_seek() {
		$bookmark_name = 'the-bookmark';
		$processor     = WP_HTML_Processor::create_full_parser( '<html><body><div>' );
		$this->assertTrue( $processor->next_tag( 'BODY' ) );
		$this->assertTrue( $processor->set_bookmark( $bookmark_name ), 'Failed to set bookmark.' );
		$this->assertTrue( $processor->has_bookmark( $bookmark_name ), 'Failed has_bookmark check.' );

		// Move past the bookmark so it must scan backwards.
		$this->assertTrue( $processor->next_tag( 'DIV' ) );

		// Confirm the bookmark works.
		$this->assertTrue( $processor->seek( $bookmark_name ), 'Failed to seek to bookmark.' );
		$this->assertSame( 'BODY', $processor->get_tag() );
	}
}
