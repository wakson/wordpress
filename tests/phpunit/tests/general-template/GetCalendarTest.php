<?php

/**
 * Tests for the get_calendar() function.
 *
 * @group functions
 *
 * @covers ::get_calendar
 */

class GetCalendarTest extends WP_UnitTestCase {
	protected $post_id;
	public function set_up() {
		parent::set_up();
		$this->post_id = self::factory()->post->create();
	}
	public function testGet_calendar_returns_calendar_html_for_current_month() {

		$current_month_calendar_html = '<caption>' . date( 'F Y' ) . '</caption>';  // Replace with realistic HTML

		$output = get_calendar( true, false );

		$this->assertStringContainsString( $current_month_calendar_html, $output );
	}


	public function testGet_calendar_returns_jan_m_is_more_than_12() {

		global $m;
		$m = 123456;
		$calendar_html = get_calendar( true, false  );
		$this->assertStringContainsString('<caption>January 1234</caption>', $calendar_html );
	}

	public function testGet_calendar_returns_sept_m_is_09() {

		global $m;
		$m = 123409;
		$calendar_html = get_calendar( true, false  );
		$this->assertStringContainsString('<caption>September 1234</caption>', $calendar_html );
	}


	public function testGet_calendar_day_of_week_starts_monday() {

		$calendar_html = get_calendar( true, false );

		// Check for the "Sunday" string within the <caption> tag or a similar check
		$this->assertMatchesRegularExpression('#title="Monday".*title="Sunday"#s',$calendar_html);
	}


	public function testGet_calendar_day_of_week_starts_sunday() {

		add_filter( 'pre_option_start_of_week', function () { return 0; });

		$expectedcalendar_html = '<th scope="col" title="Sunday">S</th>\n
                <th scope="col" title="Monday">M</th>';
		$calendar_html = get_calendar( true, false );
		$this->assertMatchesRegularExpression( '#title="Sunday".*title="Monday"#s', $calendar_html );
		// Check for the "Sunday" string within the <caption> tag or a similar check
//		$this->assertStringContainsString( $expectedcalendar_html, $calendar_html );
	}
}
