<?php
/**
 * HTML API: WP_CSS_Class_Selector class
 *
 * @package WordPress
 * @subpackage HTML-API
 * @since TBD
 */

/**
 * CSS class selector.
 *
 * This class implements a CSS class selector and is used to test for matching HTML tags
 * in a {@see WP_HTML_Tag_Processor}.
 *
 * @since TBD
 *
 * @access private
 */
final class WP_CSS_Class_Selector implements WP_CSS_HTML_Tag_Processor_Matcher {
	/**
	 * The class name to match.
	 *
	 * @var string
	 */
	public $class_name;

	/**
	 * Constructor.
	 *
	 * @param string $class_name The class name to match.
	 */
	public function __construct( string $class_name ) {
		$this->class_name = $class_name;
	}

	/**
	 * Determines if the processor's current position matches the selector.
	 *
	 * @param WP_HTML_Tag_Processor $processor The processor.
	 * @return bool True if the processor's current position matches the selector.
	 */
	public function matches( WP_HTML_Tag_Processor $processor ): bool {
		return (bool) $processor->has_class( $this->class_name );
	}
}
