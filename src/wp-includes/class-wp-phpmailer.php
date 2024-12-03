<?php
/**
 * PHPMailer extensions for WordPress
 *
 * @package WordPress
 * @since 6.7.1
 */

/**
 * PHPMailer child class
 *
 * Overrides the internationalization method in order to use WordPress' instead.
 *
 * @since 6.7.1
 */
class WP_PHPMailer extends PHPMailer\PHPMailer\PHPMailer {
	/**
	 * Public constructor
	 *
	 * @param bool $exceptions Whether to throw exceptions for errors.
	 */
	function __construct( $exceptions = false ) {
		parent::__construct( $exceptions );
		$this->SetLanguage();
	}

	/**
	 * Defines the error messages using WordPress' internationalization method.
	 *
	 * @return bool Always returns true in order to mimic PHPMailer's setLanguage() behavior
	 */
	function SetLanguage( $langcode = 'en', $lang_path = '' ) {
		$error_strings  = array(
			'authenticate'         => __( 'SMTP Error: Could not authenticate.' ),
			'buggy_php'            => __(
				'Your version of PHP is affected by a bug that may result in corrupted messages. ' .
				'To fix it, switch to sending using SMTP, disable the mail.add_x_header option in your php.ini, ' .
				'switch to MacOS or Linux, or upgrade your PHP to version 7.0.17+ or 7.1.3+.'
			),
			'connect_host'         => __( 'SMTP Error: Could not connect to SMTP host.' ),
			'data_not_accepted'    => __( 'SMTP Error: data not accepted.' ),
			'empty_message'        => __( 'Message body empty' ),
			'encoding'             => __( 'Unknown encoding: ' ),
			'execute'              => __( 'Could not execute: ' ),
			'extension_missing'    => __( 'Extension missing: ' ),
			'file_access'          => __( 'Could not access file: ' ),
			'file_open'            => __( 'File Error: Could not open file: ' ),
			'from_failed'          => __( 'The following From address failed: ' ),
			'instantiate'          => __( 'Could not instantiate mail function.' ),
			'invalid_address'      => __( 'Invalid address: ' ),
			'invalid_header'       => __( 'Invalid header name or value' ),
			'invalid_hostentry'    => __( 'Invalid hostentry: ' ),
			'invalid_host'         => __( 'Invalid host: ' ),
			'mailer_not_supported' => __( ' mailer is not supported.' ),
			'provide_address'      => __( 'You must provide at least one recipient email address.' ),
			'recipients_failed'    => __( 'SMTP Error: The following recipients failed: ' ),
			'signing'              => __( 'Signing Error: ' ),
			'smtp_code'            => __( 'SMTP code: ' ),
			'smtp_code_ex'         => __( 'Additional SMTP info: ' ),
			'smtp_connect_failed'  => __( 'SMTP connect() failed.' ),
			'smtp_detail'          => __( 'Detail: ' ),
			'smtp_error'           => __( 'SMTP server error: ' ),
			'variable_set'         => __( 'Cannot set or reset variable: ' ),
		);
		$this->language = $error_strings;
		return true;
	}
}
