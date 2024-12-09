<?php

/**
 * This corresponds to <complex-selector> in the grammar.
 *
 * > <complex-selector> = <compound-selector> [ <combinator>? <compound-selector> ] *
 */
final class WP_CSS_Complex_Selector implements WP_CSS_HTML_Processor_Matcher {
	const COMBINATOR_CHILD              = '>';
	const COMBINATOR_DESCENDANT         = ' ';
	const COMBINATOR_NEXT_SIBLING       = '+';
	const COMBINATOR_SUBSEQUENT_SIBLING = '~';

	/**
	 * The "self selector" is the last element in a complex selector, it corresponds to the
	 * selected element.
	 *
	 * @example
	 *
	 *                   $self_selector
	 *                   ┏━━━━┻━━━━┓
	 *     .heading h1 > el.selected
	 *
	 * @readonly
	 * @var WP_CSS_Compound_Selector
	 */
	public $self_selector;

	/**
	 * The "context selectors" are zero or more elements that provide additional constraints for
	 * the "self selector."
	 *
	 * In this example selector, and element like `<el class="selected">` is selected iff:
	 *   - it is a child of an `H1` element
	 *   - *and* that `H1` element is a descendant of a `HEADING` element.
	 *
	 * The `H1` and `HEADING` parts of this selector are the "context selectors." Note that this
	 * terminology is used for purposes of this class but does not correspond to language in the
	 * CSS or selector specifications.
	 *
	 * @example
	 *
	 *     $context_selectors
	 *     ┏━━━━━━┻━━━━┓
	 *     .heading h1 > el.selected
	 *
	 * The example would have the following relative selectors (note that the order is reversed):
	 *
	 * @example
	 *
	 *     array (
	 *       array(
	 *         WP_CSS_Type_Selector( 'ident' => 'h1' ),
	 *         '>', // WP_CSS_Complex_Selector::COMBINATOR_CHILD
	 *       ),
	 *       array(
	 *         new WP_CSS_Type_Selector( 'header' ),
	 *         ' ', // WP_CSS_Complex_Selector::COMBINATOR_DESCENDANT
	 *       ),
	 *     )
	 *
	 * @readonly
	 * @var array{WP_CSS_Type_Selector, string}[]|null
	 */
	public $context_selectors;

	/**
	 * @param WP_CSS_Compound_Selector $self_selector
	 * @param array{WP_CSS_Type_Selector, string}[]|null $selectors
	 */
	public function __construct(
		WP_CSS_Compound_Selector $self_selector,
		?array $context_selectors
	) {
		$this->self_selector     = $self_selector;
		$this->context_selectors = $context_selectors;
	}

	public function matches( WP_HTML_Processor $processor ): bool {
		// First selector must match this location.
		if ( ! $this->self_selector->matches( $processor ) ) {
			return false;
		}

		if ( null === $this->context_selectors || array() === $this->context_selectors ) {
			return true;
		}

		/** @var string[] */
		$breadcrumbs = array_slice( array_reverse( $processor->get_breadcrumbs() ), 1 );
		return $this->explore_matches( $this->context_selectors, $breadcrumbs );
	}

	/**
	 * This only looks at breadcrumbs and can therefore only support type selectors.
	 *
	 * @param array{WP_CSS_Type_Selector, string}[] $selectors
	 * @param string[] $breadcrumbs
	 */
	private function explore_matches( array $selectors, array $breadcrumbs ): bool {
		if ( array() === $selectors ) {
			return true;
		}
		if ( array() === $breadcrumbs ) {
			return false;
		}

		$selector   = $selectors[0][0];
		$combinator = $selectors[0][1];

		switch ( $combinator ) {
			case self::COMBINATOR_CHILD:
				if ( $selector->matches_tag( $breadcrumbs[0] ) ) {
					return $this->explore_matches( array_slice( $selectors, 1 ), array_slice( $breadcrumbs, 1 ) );
				}
				return false;

			case self::COMBINATOR_DESCENDANT:
				// Find _all_ the breadcrumbs that match and recurse from each of them.
				for ( $i = 0; $i < count( $breadcrumbs ); $i++ ) {
					if ( $selector->matches_tag( $breadcrumbs[ $i ] ) ) {
						$next_breadcrumbs = array_slice( $breadcrumbs, $i + 1 );
						if ( $this->explore_matches( array_slice( $selectors, 1 ), $next_breadcrumbs ) ) {
							return true;
						}
					}
				}
				return false;

			default:
				throw new Exception( "Unsupported combinator '{$combinator}' found." );
		}
	}
}
