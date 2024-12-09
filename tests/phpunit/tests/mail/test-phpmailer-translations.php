<?php

class Test_PHPMailer_Translations extends WP_UnitTestCase {

	private $original_locale;
	private $mo_file;

	public function set_up(): void {
		parent::set_up();

		$this->original_locale = get_locale();
		$this->mo_file         = DIR_TESTDATA . '/languages/de_DE.mo';

		load_textdomain( 'default', $this->mo_file );
		switch_to_locale( 'de_DE' );
	}

	public function tear_down(): void {
		switch_to_locale( $this->original_locale );
		unload_textdomain( 'default' );

		parent::tear_down();
	}

	/**
	 * @ticket 23311
	 */
	public function test_wp_phpmailer_error_message_keys_match() {

		$phpmailer = new PHPMailer\PHPMailer\PHPMailer();
		$phpmailer->SetLanguage();

		$wp_phpmailer = new MockPHPMailer();

		$this->assertEqualSets( array_keys( $phpmailer->GetTranslations() ), array_keys( $wp_phpmailer->GetTranslations() ) );
	}

	/**
	 * @ticket 23311
	 */
	public function test_phpmailer_error_messages_translation() {
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
