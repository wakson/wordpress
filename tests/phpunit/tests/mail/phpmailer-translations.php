<?php
/**
 * Unit tests covering PHPMailer translations.
 *
 * @package WordPress
 * @subpackage PHPMailer
 * @since 6.8.0
 */

/**
 * Class Test_PHPMailer_Translations.
 *
 * Provides tests for PHPMailer translations.
 *
 * @group phpmailer
 *
 * @since 6.8.0
 */
class Test_PHPMailer_Translations extends WP_UnitTestCase {

	/**
	 * Stores the original locale before switching for testing.
	 *
	 * @var string
	 */
	private $original_locale;

	/**
	 * Sets up the test fixture.
	 *
	 * Switches locale for PHPMailer translation tests and resets the PHPMailer instance.
	 */
	public function set_up() {
		parent::set_up();

		$this->original_locale = get_locale();
		switch_to_locale( 'de_DE' );
		reset_phpmailer_instance();
	}

	/**
	 * Tears down the test fixture.
	 *
	 * Resets the PHPMailer instance.
	 */
	public function tear_down() {
		reset_phpmailer_instance();
		parent::tear_down();
	}

	/**
	 * Test PHPMailer error messages translation for missing recipient.
	 *
	 * @link https://core.trac.wordpress.org/ticket/23311
	 */
	public function test_phpmailer_error_messages_translation_missing_recipient() {

		$phpmailer = tests_retrieve_phpmailer_instance();
		$phpmailer->setFrom( 'invalid-email@example.com' );

		try {
			$phpmailer->send();
			$this->fail( 'Expected exception was not thrown' );
		} catch ( PHPMailer\PHPMailer\Exception $e ) {
			$error_message = $e->getMessage();
		}

		$this->assertNotEquals(
			'You must provide at least one recipient email address.',
			$error_message,
			'Error message should be translated'
		);

		$this->assertEquals(
			'Sie müssen mindestens eine Empfänger-E-Mail-Adresse angeben.',
			$error_message,
			'Error message translation does not match expected French text'
		);

		switch_to_locale( $this->original_locale );
	}

	/**
	 * Test that PHPMailer error message keys are consistent across implementations.
	 *
	 * @link https://core.trac.wordpress.org/ticket/23311
	 */
	public function test_wp_phpmailer_error_message_keys_match() {

		$phpmailer    = new PHPMailer\PHPMailer\PHPMailer();
		$wp_phpmailer = tests_retrieve_phpmailer_instance();

		$this->assertEqualSets( array_keys( $phpmailer->GetTranslations() ), array_keys( $wp_phpmailer->GetTranslations() ) );
	}
}
