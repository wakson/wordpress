<?php
/**
 * HTML API: WP_CSS_Compound_Selector class
 *
 * @package WordPress
 * @subpackage HTML-API
 * @since TBD
 */

/**
 * CSS compound selector.
 *
 * This class is used to test for matching HTML tags in a {@see WP_HTML_Tag_Processor}.
 *
 * A compound selector is a combination of:
 *   - An optional type selector.
 *   - Zero or more subclass selectors (ID, class, or attribute selectors).
 *   - At least one of the above.
 *
 * @since TBD
 *
 * @access private
 */
final class WP_CSS_Compound_Selector implements WP_CSS_HTML_Tag_Processor_Matcher {
	/**
	 * The type selector.
	 *
	 * @var WP_CSS_Type_Selector|null
	 */
	public $type_selector;

	/**
	 * The subclass selectors.
	 *
	 * Subclass selectors are ID, class, or attribute selectors.
	 *
	 * @var (WP_CSS_ID_Selector|WP_CSS_Class_Selector|WP_CSS_Attribute_Selector)[]|null
	 */
	public $subclass_selectors;

	/**
	 * Constructor.
	 *
	 * @param WP_CSS_Type_Selector|null $type_selector The type selector or null.
	 * @param (WP_CSS_ID_Selector|WP_CSS_Class_Selector|WP_CSS_Attribute_Selector)[]|null $subclass_selectors
	 *        The array of subclass selectors or null.
	 */
	public function __construct( ?WP_CSS_Type_Selector $type_selector, ?array $subclass_selectors ) {
		$this->type_selector      = $type_selector;
		$this->subclass_selectors = array() === $subclass_selectors ? null : $subclass_selectors;
	}

	/**
	 * Determines if the processor's current position matches the selector.
	 *
	 * @param WP_HTML_Tag_Processor $processor The processor.
	 * @return bool True if the processor's current position matches the selector.
	 */
	public function matches( WP_HTML_Tag_Processor $processor ): bool {
		if ( $this->type_selector && ! $this->type_selector->matches( $processor ) ) {
			return false;
		}
		if ( null !== $this->subclass_selectors ) {
			foreach ( $this->subclass_selectors as $subclass_selector ) {
				if ( ! $subclass_selector->matches( $processor ) ) {
					return false;
				}
			}
		}
		return true;
	}
}
