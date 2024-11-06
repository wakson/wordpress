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
	 * @ticket 62290
	 */
	public function test_full_processor_seek_same_location() {
		$bookmark_name = 'the-bookmark';
		$processor     = WP_HTML_Processor::create_full_parser( '<html><body><div>' );
		$this->assertTrue( $processor->next_tag( 'BODY' ) );
		$this->assertTrue( $processor->set_bookmark( $bookmark_name ), 'Failed to set bookmark.' );
		$this->assertTrue( $processor->has_bookmark( $bookmark_name ), 'Failed has_bookmark check.' );

		// Confirm the bookmark works.
		$this->assertTrue( $processor->seek( $bookmark_name ), 'Failed to seek to bookmark.' );
		$this->assertSame( 'BODY', $processor->get_tag() );
		$this->assertTrue( $processor->next_tag() );
		$this->assertSame( 'DIV', $processor->get_tag() );
		$this->assertSame( array( 'HTML', 'BODY', 'DIV' ), $processor->get_breadcrumbs() );
	}

	/**
	 * @ticket 62290
	 */
	public function test_full_processor_seek_backward() {
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

	/**
	 * @ticket 62290
	 */
	public function test_full_processor_seek_forward() {
		$processor = WP_HTML_Processor::create_full_parser( '<html><body><div one></div><div two>' );
		$this->assertTrue( $processor->next_tag( 'BODY' ) );
		$this->assertTrue( $processor->set_bookmark( 'body' ), 'Failed to set bookmark "body".' );
		$this->assertTrue( $processor->has_bookmark( 'body' ), 'Failed "body" has_bookmark check.' );

		// Move past the bookmark so it must scan backwards.
		$this->assertTrue( $processor->next_tag( 'DIV' ) );
		$this->assertTrue( $processor->get_attribute( 'one' ) );
		$this->assertTrue( $processor->set_bookmark( 'one' ), 'Failed to set bookmark "one".' );
		$this->assertTrue( $processor->has_bookmark( 'one' ), 'Failed "one" has_bookmark check.' );

		// Seek back.
		$this->assertTrue( $processor->seek( 'body' ), 'Failed to seek to bookmark "body".' );
		$this->assertSame( 'BODY', $processor->get_tag() );

		// Seek forward and continue processing.
		$this->assertTrue( $processor->seek( 'one' ), 'Failed to seek to bookmark "one".' );
		$this->assertSame( 'DIV', $processor->get_tag() );
		$this->assertTrue( $processor->get_attribute( 'one' ) );
		$this->assertTrue( $processor->next_tag() );

		$this->assertSame( 'DIV', $processor->get_tag() );
		$this->assertTrue( $processor->get_attribute( 'two' ) );
	}
}
