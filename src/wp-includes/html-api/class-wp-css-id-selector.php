<?php
/**
 * HTML API: WP_CSS_ID_Selector class
 *
 * @package WordPress
 * @subpackage HTML-API
 * @since TBD
 */

/**
 * CSS ID selector.
 *
 * This class implements a CSS ID selector and is used to test for matching HTML tags
 * in a {@see WP_HTML_Tag_Processor}.
 *
 * @since TBD
 *
 * @access private
 */
final class WP_CSS_ID_Selector implements WP_CSS_HTML_Tag_Processor_Matcher {
	/**
	 * The ID to match.
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Constructor.
	 *
	 * @param string $id The ID to match.
	 */
	public function __construct( string $id ) {
		$this->id = $id;
	}

	/**
	 * Determines if the processor's current position matches the selector.
	 *
	 * @param WP_HTML_Tag_Processor $processor The processor.
	 * @return bool True if the processor's current position matches the selector.
	 */
	public function matches( WP_HTML_Tag_Processor $processor ): bool {
		$id = $processor->get_attribute( 'id' );
		if ( ! is_string( $id ) ) {
			return false;
		}

		$case_insensitive = $processor->is_quirks_mode();

		return $case_insensitive
			? 0 === strcasecmp( $id, $this->id )
			: $processor->get_attribute( 'id' ) === $this->id;
	}
}
