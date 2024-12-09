<?php
/**
 * HTML API: WP_CSS_Type_Selector class
 *
 * @package WordPress
 * @subpackage HTML-API
 * @since TBD
 */

/**
 * CSS type selector.
 *
 * This class is used to test for matching HTML tags in a {@see WP_HTML_Tag_Processor}.
 *
 * @since TBD
 *
 * @access private
 */
final class WP_CSS_Type_Selector implements WP_CSS_HTML_Tag_Processor_Matcher {
	/**
	 * The element type (tag name) to match or '*' to match any element.
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Constructor.
	 *
	 * @param string $type The element type (tag name) to match or '*' to match any element.
	 */
	public function __construct( string $type ) {
		$this->type = $type;
	}

	/**
	 * Determines if the processor's current position matches the selector.
	 *
	 * @param WP_HTML_Tag_Processor $processor The processor.
	 * @return bool True if the processor's current position matches the selector.
	 */
	public function matches( WP_HTML_Tag_Processor $processor ): bool {
		$tag_name = $processor->get_tag();
		if ( null === $tag_name ) {
			return false;
		}
		return $this->matches_tag( $tag_name );
	}

	/**
	 * Checks whether the selector matches the provided tag name.
	 *
	 * @param string $tag_name
	 * @return bool
	 */
	public function matches_tag( string $tag_name ): bool {
		if ( '*' === $this->type ) {
			return true;
		}
		return 0 === strcasecmp( $tag_name, $this->type );
	}
}
