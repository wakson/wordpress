<?php

class Test_PHPMailer_Translations extends WP_UnitTestCase {
	/**
	 * @ticket 23311
	 */
	public function test_wp_phpmailer_error_message_keys_match() {
		require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
		require_once ABSPATH . WPINC . '/class-wp-phpmailer.php';

		$phpmailer = new PHPMailer\PHPMailer\PHPMailer();
		$phpmailer->SetLanguage();

		$wp_phpmailer = new WP_PHPMailer();

		$this->assertTrue( array_keys( $phpmailer->GetTranslations() ) === array_keys( $wp_phpmailer->GetTranslations() ) );
	}
}
