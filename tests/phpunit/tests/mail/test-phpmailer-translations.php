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
	 * Path to the German translation (.mo) file used for testing.
	 *
	 * @var string
	 */
	private $mo_file;

	/**
	 * Sets up the test fixture.
	 *
	 * Loads translation domain and switches locale for PHPMailer translation tests.
	 */
	public function set_up() {
		parent::set_up();

		$this->original_locale = get_locale();
		$this->mo_file         = DIR_TESTDATA . '/languages/de_DE.mo';

		load_textdomain( 'default', $this->mo_file );
		switch_to_locale( 'de_DE' );
	}

	/**
	 * Tears down the test fixture.
	 *
	 * Restores the original locale and unloads the translation domain.
	 */
	public function tear_down() {
		switch_to_locale( $this->original_locale );
		unload_textdomain( 'default' );

		parent::tear_down();
	}

	/**
	 * Test that PHPMailer error message keys are consistent across implementations.
	 *
	 * @link https://core.trac.wordpress.org/ticket/23311
	 */
	public function test_wp_phpmailer_error_message_keys_match() {

		$phpmailer = new PHPMailer\PHPMailer\PHPMailer();
		$phpmailer->SetLanguage();

		$wp_phpmailer = new MockPHPMailer();

		$this->assertEqualSets( array_keys( $phpmailer->GetTranslations() ), array_keys( $wp_phpmailer->GetTranslations() ) );
	}

	/**
	 * Test PHPMailer error messages translation for missing recipient.
	 *
	 * @link https://core.trac.wordpress.org/ticket/23311
	 */
	public function test_phpmailer_error_messages_translation_missing_recipient() {
		$phpmailer = new WP_PHPMailer( true );
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
	}
}
