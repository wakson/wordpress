<?php

/**
 * @group post
 * @group media
 */
class Tests_Post_Thumbnail_Template extends WP_UnitTestCase {
	protected static $post;
	protected static $different_post;
	protected static $attachment_id;

	protected $current_size_filter_data   = null;
	protected $current_size_filter_result = null;

	public static function wpSetUpBeforeClass( WP_UnitTest_Factory $factory ) {
		self::$post           = $factory->post->create_and_get();
		self::$different_post = $factory->post->create_and_get();

		$file                = DIR_TESTDATA . '/images/canola.jpg';
		self::$attachment_id = $factory->attachment->create_upload_object(
			$file,
			self::$post->ID,
			array(
				'post_mime_type' => 'image/jpeg',
			)
		);
	}

	public static function tear_down_after_class() {
		wp_delete_attachment( self::$attachment_id, true );
		parent::tear_down_after_class();
	}

	public function test_has_post_thumbnail() {
		$this->assertFalse( has_post_thumbnail( self::$post ) );
		$this->assertFalse( has_post_thumbnail( self::$post->ID ) );
		$this->assertFalse( has_post_thumbnail() );

		$GLOBALS['post'] = self::$post;

		$this->assertFalse( has_post_thumbnail() );

		unset( $GLOBALS['post'] );

		set_post_thumbnail( self::$post, self::$attachment_id );

		$this->assertTrue( has_post_thumbnail( self::$post ) );
		$this->assertTrue( has_post_thumbnail( self::$post->ID ) );
		$this->assertFalse( has_post_thumbnail() );

		$GLOBALS['post'] = self::$post;

		$this->assertTrue( has_post_thumbnail() );
	}

	public function test_get_post_thumbnail_id() {
		$this->assertSame( 0, get_post_thumbnail_id( self::$post ) );
		$this->assertSame( 0, get_post_thumbnail_id( self::$post->ID ) );
		$this->assertFalse( get_post_thumbnail_id() );

		set_post_thumbnail( self::$post, self::$attachment_id );

		$this->assertSame( self::$attachment_id, get_post_thumbnail_id( self::$post ) );
		$this->assertSame( self::$attachment_id, get_post_thumbnail_id( self::$post->ID ) );

		$GLOBALS['post'] = self::$post;

		$this->assertSame( self::$attachment_id, get_post_thumbnail_id() );
	}

	/**
	 * Ensure `update_post_thumbnail_cache()` works when querying post objects.
	 *
	 * @ticket 59521
	 * @ticket 30017
	 * @ticket 33968
	 *
	 * @covers ::update_post_thumbnail_cache
	 */
	public function test_update_post_thumbnail_cache_when_querying_full_post_objects() {
		set_post_thumbnail( self::$post, self::$attachment_id );

		// Test case where `$query->posts` should return Array of post objects.
		$query = new WP_Query(
			array(
				'post_type' => 'any',
				'post__in'  => array( self::$post->ID ),
				'orderby'   => 'post__in',
			)
		);

		$this->assertFalse( $query->thumbnails_cached, 'Thumbnails should not be cached prior to calling update_post_thumbnail_cache().' );

		update_post_thumbnail_cache( $query );

		$this->assertTrue( $query->thumbnails_cached, 'Thumbnails should be cached after calling update_post_thumbnail_cache().' );
	}

	/**
	 * Ensure `update_post_thumbnail_cache()` works when querying post IDs.
	 *
	 * @ticket 59521
	 *
	 * @covers ::update_post_thumbnail_cache
	 */
	public function test_update_post_thumbnail_cache_when_querying_post_id_field() {
		set_post_thumbnail( self::$post, self::$attachment_id );

		// Test case where `$query2->posts` should return Array of post IDs.
		$query = new WP_Query(
			array(
				'post_type' => 'any',
				'post__in'  => array( self::$post->ID ),
				'orderby'   => 'post__in',
				'fields'    => 'ids',
			)
		);

		$this->assertFalse( $query->thumbnails_cached, 'Thumbnails should not be cached prior to calling update_post_thumbnail_cache().' );

		update_post_thumbnail_cache( $query );

		$this->assertTrue( $query->thumbnails_cached, 'Thumbnails should be cached after calling update_post_thumbnail_cache().' );
	}

	/**
	 * @ticket 12235
	 */
	public function test_get_the_post_thumbnail_caption() {
		$this->assertSame( '', get_the_post_thumbnail_caption() );

		$caption = 'This is a caption.';

		$post_id       = self::factory()->post->create();
		$attachment_id = self::factory()->attachment->create_object(
			'image.jpg',
			$post_id,
			array(
				'post_mime_type' => 'image/jpeg',
				'post_type'      => 'attachment',
				'post_excerpt'   => $caption,
			)
		);

		set_post_thumbnail( $post_id, $attachment_id );

		$this->assertSame( $caption, get_the_post_thumbnail_caption( $post_id ) );
	}

	/**
	 * @ticket 12235
	 */
	public function test_get_the_post_thumbnail_caption_empty() {
		$post_id       = self::factory()->post->create();
		$attachment_id = self::factory()->attachment->create_object(
			'image.jpg',
			$post_id,
			array(
				'post_mime_type' => 'image/jpeg',
				'post_type'      => 'attachment',
				'post_excerpt'   => '',
			)
		);

		set_post_thumbnail( $post_id, $attachment_id );

		$this->assertSame( '', get_the_post_thumbnail_caption( $post_id ) );
	}

	/**
	 * @ticket 12235
	 */
	public function test_the_post_thumbnail_caption() {
		$caption = 'This is a caption.';

		$post_id       = self::factory()->post->create();
		$attachment_id = self::factory()->attachment->create_object(
			'image.jpg',
			$post_id,
			array(
				'post_mime_type' => 'image/jpeg',
				'post_type'      => 'attachment',
				'post_excerpt'   => $caption,
			)
		);

		set_post_thumbnail( $post_id, $attachment_id );

		$this->expectOutputString( $caption );
		the_post_thumbnail_caption( $post_id );
	}

	public function test_get_the_post_thumbnail() {
		$this->assertSame( '', get_the_post_thumbnail() );
		$this->assertSame( '', get_the_post_thumbnail( self::$post ) );
		set_post_thumbnail( self::$post, self::$attachment_id );

		$expected = wp_get_attachment_image(
			self::$attachment_id,
			'post-thumbnail',
			false,
			array(
				'class' => 'attachment-post-thumbnail size-post-thumbnail wp-post-image',
			)
		);

		$this->assertSame( $expected, get_the_post_thumbnail( self::$post ) );

		$GLOBALS['post'] = self::$post;

		$this->assertSame( $expected, get_the_post_thumbnail() );
	}

	public function test_the_post_thumbnail() {

		$this->expectOutputString( '' );
		the_post_thumbnail();

		$GLOBALS['post'] = self::$post;

		$this->expectOutputString( '' );
		the_post_thumbnail();

		set_post_thumbnail( self::$post, self::$attachment_id );

		$expected = wp_get_attachment_image(
			self::$attachment_id,
			'post-thumbnail',
			false,
			array(
				'class' => 'attachment-post-thumbnail size-post-thumbnail wp-post-image',
			)
		);

		$this->expectOutputString( $expected );
		the_post_thumbnail();
	}

	/**
	 * @ticket 33070
	 */
	public function test_get_the_post_thumbnail_url() {
		$this->assertFalse( has_post_thumbnail( self::$post ) );
		$this->assertFalse( get_the_post_thumbnail_url() );
		$this->assertFalse( get_the_post_thumbnail_url( self::$post ) );

		set_post_thumbnail( self::$post, self::$attachment_id );

		$this->assertFalse( get_the_post_thumbnail_url() );
		$this->assertSame( wp_get_attachment_url( self::$attachment_id ), get_the_post_thumbnail_url( self::$post ) );

		$GLOBALS['post'] = self::$post;

		$this->assertSame( wp_get_attachment_url( self::$attachment_id ), get_the_post_thumbnail_url() );
	}

	/**
	 * @ticket 33070
	 */
	public function test_get_the_post_thumbnail_url_with_invalid_post() {
		set_post_thumbnail( self::$post, self::$attachment_id );

		$this->assertNotFalse( get_the_post_thumbnail_url( self::$post->ID ) );

		$deleted = wp_delete_post( self::$post->ID, true );
		$this->assertNotEmpty( $deleted );

		$this->assertFalse( get_the_post_thumbnail_url( self::$post->ID ) );
	}

	/**
	 * @ticket 33070
	 */
	public function test_the_post_thumbnail_url() {
		$GLOBALS['post'] = self::$post;

		$this->expectOutputString( '' );
		the_post_thumbnail_url();

		set_post_thumbnail( self::$post, self::$attachment_id );

		$this->expectOutputString( wp_get_attachment_url( self::$attachment_id ) );
		the_post_thumbnail_url();
	}

	/**
	 * @ticket 12922
	 */
	public function test__wp_preview_post_thumbnail_filter() {
		$old_post = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;

		$GLOBALS['post']           = self::$post;
		$_REQUEST['_thumbnail_id'] = self::$attachment_id;
		$_REQUEST['preview_id']    = self::$post->ID;

		$result = _wp_preview_post_thumbnail_filter( '', self::$post->ID, '_thumbnail_id' );

		// Clean up.
		$GLOBALS['post'] = $old_post;
		unset( $_REQUEST['_thumbnail_id'] );
		unset( $_REQUEST['preview_id'] );

		$this->assertEquals( self::$attachment_id, $result );
	}

	/**
	 * @ticket 37697
	 */
	public function test__wp_preview_post_thumbnail_filter_secondary_post() {
		$old_post = isset( $GLOBALS['post'] ) ? $GLOBALS['post'] : null;

		$secondary_post = self::factory()->post->create(
			array(
				'post_stauts' => 'publish',
			)
		);

		$GLOBALS['post']           = self::$post;
		$_REQUEST['_thumbnail_id'] = self::$attachment_id;
		$_REQUEST['preview_id']    = $secondary_post;

		$result = _wp_preview_post_thumbnail_filter( '', self::$post->ID, '_thumbnail_id' );

		// Clean up.
		$GLOBALS['post'] = $old_post;
		unset( $_REQUEST['_thumbnail_id'] );
		unset( $_REQUEST['preview_id'] );

		$this->assertEmpty( $result );
	}

	/**
	 * @ticket 12922
	 */
	public function test_insert_post_with_post_thumbnail() {
		$post_id = wp_insert_post(
			array(
				'ID'            => self::$post->ID,
				'post_status'   => 'publish',
				'post_content'  => 'Post content',
				'post_title'    => 'Post Title',
				'_thumbnail_id' => self::$attachment_id,
			)
		);

		$thumbnail_id = get_post_thumbnail_id( $post_id );
		$this->assertSame( self::$attachment_id, $thumbnail_id );

		$post_id = wp_insert_post(
			array(
				'ID'            => $post_id,
				'post_status'   => 'publish',
				'post_content'  => 'Post content',
				'post_title'    => 'Post Title',
				'_thumbnail_id' => - 1, // -1 removes post thumbnail.
			)
		);

		$thumbnail_id = get_post_thumbnail_id( $post_id );
		$this->assertEmpty( $thumbnail_id );
	}

	/**
	 * @ticket 37658
	 */
	public function test_insert_attachment_with_post_thumbnail() {
		// Audio files support featured images.
		$post_id = wp_insert_post(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_content'   => 'Post content',
				'post_title'     => 'Post Title',
				'post_mime_type' => 'audio/mpeg',
				'post_parent'    => 0,
				'file'           => DIR_TESTDATA . '/audio/test-noise.mp3', // File does not exist, but does not matter here.
				'_thumbnail_id'  => self::$attachment_id,
			)
		);

		$thumbnail_id = get_post_thumbnail_id( $post_id );
		$this->assertSame( self::$attachment_id, $thumbnail_id );

		// Images do not support featured images.
		$post_id = wp_insert_post(
			array(
				'post_type'      => 'attachment',
				'post_status'    => 'inherit',
				'post_content'   => 'Post content',
				'post_title'     => 'Post Title',
				'post_mime_type' => 'image/jpeg',
				'post_parent'    => 0,
				'file'           => DIR_TESTDATA . '/images/canola.jpg',
				'_thumbnail_id'  => self::$attachment_id,
			)
		);

		$thumbnail_id = get_post_thumbnail_id( $post_id );
		$this->assertEmpty( $thumbnail_id );
	}

	/**
	 * @ticket 39030
	 */
	public function test_post_thumbnail_size_filter_simple() {
		$this->current_size_filter_data = 'medium';

		add_filter( 'post_thumbnail_size', array( $this, 'filter_post_thumbnail_size' ), 10, 2 );

		// This filter is used to capture the $size result.
		add_filter( 'post_thumbnail_html', array( $this, 'filter_set_post_thumbnail_size_result' ), 10, 4 );
		get_the_post_thumbnail( self::$post );

		$result = $this->current_size_filter_result;

		$this->current_size_filter_data   = null;
		$this->current_size_filter_result = null;

		$this->assertSame( 'medium', $result );
	}

	/**
	 * @ticket 39030
	 * @dataProvider data_post_thumbnail_size_filter_complex
	 */
	public function test_post_thumbnail_size_filter_complex( $which_post, $expected ) {
		$this->current_size_filter_data = array(
			self::$post->ID           => 'medium',
			self::$different_post->ID => 'thumbnail',
		);

		$post = 1 === $which_post ? self::$different_post : self::$post;

		add_filter( 'post_thumbnail_size', array( $this, 'filter_post_thumbnail_size' ), 10, 2 );

		// This filter is used to capture the $size result.
		add_filter( 'post_thumbnail_html', array( $this, 'filter_set_post_thumbnail_size_result' ), 10, 4 );
		get_the_post_thumbnail( $post );

		$result = $this->current_size_filter_result;

		$this->current_size_filter_data   = null;
		$this->current_size_filter_result = null;

		$this->assertSame( $expected, $result );
	}

	/**
	 * @ticket 57490
	 */
	public function test_get_the_post_thumbnail_includes_loading_lazy() {
		set_post_thumbnail( self::$post, self::$attachment_id );

		$html = get_the_post_thumbnail( self::$post );
		$this->assertStringContainsString( ' loading="lazy"', $html );
	}

	/**
	 * @ticket 57490
	 */
	public function test_get_the_post_thumbnail_respects_passed_loading_attr() {
		set_post_thumbnail( self::$post, self::$attachment_id );

		$html = get_the_post_thumbnail( self::$post, 'post-thumbnail', array( 'loading' => 'eager' ) );
		$this->assertStringContainsString( ' loading="eager"', $html, 'loading=eager was not present in img tag because attributes array with loading=eager was overwritten.' );

		$html = get_the_post_thumbnail( self::$post, 'post-thumbnail', 'loading=eager' );
		$this->assertStringContainsString( ' loading="eager"', $html, 'loading=eager was not present in img tag because attributes string with loading=eager was overwritten.' );
	}

	/**
	 * @ticket 57490
	 */
	public function test_get_the_post_thumbnail_respects_wp_lazy_loading_enabled_filter() {
		set_post_thumbnail( self::$post, self::$attachment_id );

		add_filter( 'wp_lazy_loading_enabled', '__return_false' );

		$html = get_the_post_thumbnail( self::$post );
		$this->assertStringNotContainsString( ' loading="lazy"', $html );
	}

	public function data_post_thumbnail_size_filter_complex() {
		return array(
			array( 0, 'medium' ),
			array( 1, 'thumbnail' ),
		);
	}

	/**
	 * Tests that `_wp_post_thumbnail_context_filter()` returns 'the_post_thumbnail'.
	 *
	 * @ticket 58212
	 *
	 * @covers ::_wp_post_thumbnail_context_filter
	 */
	public function test_wp_post_thumbnail_context_filter_should_return_the_post_thumbnail() {
		$this->assertSame( 'the_post_thumbnail', _wp_post_thumbnail_context_filter( 'wp_get_attachment_image' ) );
	}

	/**
	 * Tests that `::_wp_post_thumbnail_context_filter_add` adds a filter to override the context
	 * used in `wp_get_attachment_image()`.
	 *
	 * @ticket 58212
	 *
	 * @covers ::_wp_post_thumbnail_context_filter_add
	 */
	public function test_wp_post_thumbnail_context_filter_add_should_add_the_filter() {
		$last_context = '';
		$this->track_last_attachment_image_context( $last_context );

		_wp_post_thumbnail_context_filter_add();
		wp_get_attachment_image( self::$attachment_id );

		$this->assertSame( 'the_post_thumbnail', $last_context );
	}

	/**
	 * Tests that `_wp_post_thumbnail_context_filter_remove()` removes a filter to override the context
	 * used in `wp_get_attachment_image()`.
	 *
	 * @ticket 58212
	 *
	 * @covers ::_wp_post_thumbnail_context_filter_remove
	 */
	public function test_wp_post_thumbnail_context_filter_remove_should_remove_the_filter() {
		$last_context = '';
		$this->track_last_attachment_image_context( $last_context );

		_wp_post_thumbnail_context_filter_add();
		wp_get_attachment_image( self::$attachment_id );

		// Verify that the filter has been added before testing that it has been removed.
		$this->assertSame(
			'the_post_thumbnail',
			$last_context,
			'The filter was not added.'
		);

		_wp_post_thumbnail_context_filter_remove();

		// The context should no longer be modified by the filter.
		wp_get_attachment_image( self::$attachment_id );

		$this->assertSame(
			'wp_get_attachment_image',
			$last_context,
			'The filter was not removed.'
		);
	}

	/**
	 * Tests that `get_the_post_thumbnail()` uses the 'the_post_thumbnail' context.
	 *
	 * @ticket 58212
	 *
	 * @covers ::get_the_post_thumbnail
	 */
	public function test_get_the_post_thumbnail_should_use_the_post_thumbnail_context() {
		$last_context = '';
		$this->track_last_attachment_image_context( $last_context );

		set_post_thumbnail( self::$post, self::$attachment_id );
		get_the_post_thumbnail( self::$post );

		$this->assertSame( 'the_post_thumbnail', $last_context );
	}

	/**
	 * Tests that `get_the_post_thumbnail()` restores the context afterwards.
	 *
	 * @ticket 58212
	 *
	 * @covers ::get_the_post_thumbnail
	 */
	public function test_get_the_post_thumbnail_should_remove_the_post_thumbnail_context_afterwards() {
		$last_context = '';
		$this->track_last_attachment_image_context( $last_context );

		set_post_thumbnail( self::$post, self::$attachment_id );
		get_the_post_thumbnail( self::$post );

		// Verify that the context was overridden before testing that it has been restored.
		$this->assertSame(
			'the_post_thumbnail',
			$last_context,
			'The context was not overridden.'
		);

		// The context should no longer be overridden.
		wp_get_attachment_image( self::$attachment_id );

		$this->assertSame(
			'wp_get_attachment_image',
			$last_context,
			'The context was not restored.'
		);
	}

	/**
	 * Helper method to keep track of the last context returned by the 'wp_get_attachment_image_context' filter.
	 *
	 * The method parameter is passed by reference and therefore will always contain the last context value.
	 *
	 * @param mixed $last_context Variable to track last context. Passed by reference.
	 */
	private function track_last_attachment_image_context( &$last_context ) {
		add_filter(
			'wp_get_attachment_image_context',
			static function ( $context ) use ( &$last_context ) {
				$last_context = $context;
				return $context;
			},
			11
		);
	}

	public function filter_post_thumbnail_size( $size, $post_id ) {
		if ( is_array( $this->current_size_filter_data ) && isset( $this->current_size_filter_data[ $post_id ] ) ) {
			return $this->current_size_filter_data[ $post_id ];
		}

		if ( is_string( $this->current_size_filter_data ) ) {
			return $this->current_size_filter_data;
		}

		return $size;
	}

	public function filter_set_post_thumbnail_size_result( $html, $post_id, $post_thumbnail_id, $size ) {
		$this->current_size_filter_result = $size;

		return $html;
	}
}
