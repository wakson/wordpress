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
	 * Test PHPMailer error messages translation for missing recipient.
	 *
	 * @link https://core.trac.wordpress.org/ticket/23311
	 */
	public function test_phpmailer_error_messages_translation_missing_recipient() {
		$is_switched = switch_to_locale( 'de_DE' );

		$phpmailer = tests_retrieve_phpmailer_instance();
		$phpmailer->setFrom( 'invalid-email@example.com' );

		try {
			$phpmailer->send();
			$this->fail( 'Expected exception was not thrown' );
		} catch ( PHPMailer\PHPMailer\Exception $e ) {
			$error_message = $e->getMessage();
		}

		if ( $is_switched ) {
			restore_previous_locale();
		}

		$this->assertSame(
			'Du musst mindestens eine Empfänger-E-Mail-Adresse angeben.',
			$error_message,
			'Error message is not translated as expected'
		);
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
