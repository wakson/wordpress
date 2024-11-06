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

	/**
	 * Seeking into and out of foreign content requires updating the parsing namespace correctly.
	 *
	 * @ticket 62290
	 */
	public function test_full_processor_seek_back_from_foreign_content() {
		$processor = WP_HTML_Processor::create_full_parser( '<html><body><custom-element /><svg><rect />' );
		$this->assertTrue( $processor->next_tag( 'CUSTOM-ELEMENT' ) );
		$this->assertTrue( $processor->set_bookmark( 'mark' ), 'Failed to set bookmark "mark".' );
		$this->assertTrue( $processor->has_bookmark( 'mark' ), 'Failed "mark" has_bookmark check.' );

		/*
		 * <custom-element /> has self-closing flag, but HTML elements (that are not void elements) cannot self-close,
		 * they must be closed by some means, usually a closing tag.
		 *
		 * If the div were interpreted as foreign content, it would self-close.
		 */
		$this->assertTrue( $processor->has_self_closing_flag() );
		$this->assertTrue( $processor->expects_closer(), 'Incorrectly interpreted custom-element with self-closing flag as self-closing element.' );

		// Proceed into foreign content.
		$this->assertTrue( $processor->next_tag( 'RECT' ) );
		$this->assertSame( 'svg', $processor->get_namespace() );
		$this->assertTrue( $processor->has_self_closing_flag() );
		$this->assertFalse( $processor->expects_closer() );
		$this->assertSame( array( 'HTML', 'BODY', 'CUSTOM-ELEMENT', 'SVG', 'RECT' ), $processor->get_breadcrumbs() );

		// Seek back.
		$this->assertTrue( $processor->seek( 'mark' ), 'Failed to seek to bookmark "mark".' );
		$this->assertSame( 'CUSTOM-ELEMENT', $processor->get_tag() );
		// If the parsing namespace were not correct here (html),
		// then the self-closing flag would be misinterpreted.
		$this->assertTrue( $processor->has_self_closing_flag() );
		$this->assertTrue( $processor->expects_closer(), 'Incorrectly interpreted custom-element with self-closing flag as self-closing element.' );

		// Proceed into foreign content again.
		$this->assertTrue( $processor->next_tag( 'RECT' ) );
		$this->assertSame( 'svg', $processor->get_namespace() );
		$this->assertTrue( $processor->has_self_closing_flag() );
		$this->assertFalse( $processor->expects_closer() );

		// The RECT should still descend from the CUSTOM-ELEMENT despite its self-closing flag.
		$this->assertSame( array( 'HTML', 'BODY', 'CUSTOM-ELEMENT', 'SVG', 'RECT' ), $processor->get_breadcrumbs() );
	}

	/**
	 * Seeking into and out of foreign content requires updating the parsing namespace correctly.
	 *
	 * @ticket 62290
	 */
	public function test_pragment_processor_seek_back_from_foreign_content() {
		$processor = WP_HTML_Processor::create_fragment( '<custom-element /><svg><rect />' );
		$this->assertTrue( $processor->next_tag( 'CUSTOM-ELEMENT' ) );
		$this->assertTrue( $processor->set_bookmark( 'mark' ), 'Failed to set bookmark "mark".' );
		$this->assertTrue( $processor->has_bookmark( 'mark' ), 'Failed "mark" has_bookmark check.' );

		/*
		 * <custom-element /> has self-closing flag, but HTML elements (that are not void elements) cannot self-close,
		 * they must be closed by some means, usually a closing tag.
		 *
		 * If the div were interpreted as foreign content, it would self-close.
		 */
		$this->assertTrue( $processor->has_self_closing_flag() );
		$this->assertTrue( $processor->expects_closer(), 'Incorrectly interpreted custom-element with self-closing flag as self-closing element.' );

		// Proceed into foreign content.
		$this->assertTrue( $processor->next_tag( 'RECT' ) );
		$this->assertSame( 'svg', $processor->get_namespace() );
		$this->assertTrue( $processor->has_self_closing_flag() );
		$this->assertFalse( $processor->expects_closer() );
		$this->assertSame( array( 'HTML', 'BODY', 'CUSTOM-ELEMENT', 'SVG', 'RECT' ), $processor->get_breadcrumbs() );

		// Seek back.
		$this->assertTrue( $processor->seek( 'mark' ), 'Failed to seek to bookmark "mark".' );
		$this->assertSame( 'CUSTOM-ELEMENT', $processor->get_tag() );
		// If the parsing namespace were not correct here (html),
		// then the self-closing flag would be misinterpreted.
		$this->assertTrue( $processor->has_self_closing_flag() );
		$this->assertTrue( $processor->expects_closer(), 'Incorrectly interpreted custom-element with self-closing flag as self-closing element.' );

		// Proceed into foreign content again.
		$this->assertTrue( $processor->next_tag( 'RECT' ) );
		$this->assertSame( 'svg', $processor->get_namespace() );
		$this->assertTrue( $processor->has_self_closing_flag() );
		$this->assertFalse( $processor->expects_closer() );

		// The RECT should still descend from the CUSTOM-ELEMENT despite its self-closing flag.
		$this->assertSame( array( 'HTML', 'BODY', 'CUSTOM-ELEMENT', 'SVG', 'RECT' ), $processor->get_breadcrumbs() );
	}
}
