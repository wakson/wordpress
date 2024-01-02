<?php

/**
 * Tests for the wp_ob_end_flush_all function.
 *
 * @group Functions
 *
 * @covers ::wp_ob_end_flush_all
 */
class Tests_Functions_wpObEndFlushAll extends WP_UnitTestCase {

	/**
	 * @ticket 60177
	 */
	public function test_wp_ob_end_flush_all() {

		ob_start();
		echo( 'output' );

		wp_ob_end_flush_all();
		$this->assertEmpty( ob_get_contents() );

		// re-start ob as wp_ob_end_flush_all clears all the OB's and PHPunit has one open
		ob_start();
	}
}
