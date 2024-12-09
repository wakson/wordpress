<?php
/**
 * HTML API: WP_CSS_Attribute_Selector class
 *
 * @package WordPress
 * @subpackage HTML-API
 * @since TBD
 */

/**
 * CSS attribute selector.
 *
 * This class implements a CSS attribute selector and is used to test for matching HTML tags
 * in a {@see WP_HTML_Tag_Processor}.
 *
 * @since TBD
 *
 * @access private
 */
final class WP_CSS_Attribute_Selector implements WP_CSS_HTML_Tag_Processor_Matcher {
	/**
	 * The attribute value is matched exactly.
	 *
	 * @example
	 *
	 *     [att=val]
	 */
	const MATCH_EXACT = 'exact';

	/**
	 * The attribute value matches any value in a whitespace separated list of words exactly.
	 *
	 * @example
	 *
	 *     [attr~=value]
	 */
	const MATCH_ONE_OF_EXACT = 'one-of';

	/**
	 * The attribute value is matched exactly or matches the beginning of the attribute
	 * immediately followed by a hyphen.
	 *
	 * @example
	 *
	 *     [attr|=value]
	 */
	const MATCH_EXACT_OR_HYPHEN_PREFIXED = 'exact-or-hyphen-prefixed';

	/**
	 * The attribute value matches the start of the attribute.
	 *
	 * @example
	 *
	 *     [attr^=value]
	 */
	const MATCH_PREFIXED_BY = 'prefixed';

	/**
	 * The attribute value matches the end of the attribute.
	 *
	 * @example
	 *
	 *     [attr$=value]
	 */
	const MATCH_SUFFIXED_BY = 'suffixed';

	/**
	 * The attribute value is contained in the attribute.
	 *
	 * @example
	 *
	 *     [attr*=value]
	 */
	const MATCH_CONTAINS = 'contains';

	/**
	 * Modifier for case sensitive matching.
	 *
	 * @example
	 *
	 *     [attr=value s]
	 */
	const MODIFIER_CASE_SENSITIVE = 'case-sensitive';

	/**
	 * Modifier for case insensitive matching.
	 *
	 * @example
	 *
	 *     [attr=value i]
	 */
	const MODIFIER_CASE_INSENSITIVE = 'case-insensitive';

	/**
	 * The name of the attribute to match.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * The attribute matcher.
	 *
	 * Allowed string values are the class constants:
	 *   - {@see WP_CSS_Attribute_Selector::MATCH_EXACT}
	 *   - {@see WP_CSS_Attribute_Selector::MATCH_ONE_OF_EXACT}
	 *   - {@see WP_CSS_Attribute_Selector::MATCH_EXACT_OR_HYPHEN_PREFIXED}
	 *   - {@see WP_CSS_Attribute_Selector::MATCH_PREFIXED_BY}
	 *   - {@see WP_CSS_Attribute_Selector::MATCH_SUFFIXED_BY}
	 *   - {@see WP_CSS_Attribute_Selector::MATCH_CONTAINS}
	 *
	 * @var string|null
	 */
	public $matcher;

	/**
	 * The attribute value to match.
	 *
	 * @var string|null
	 */
	public $value;

	/**
	 * The attribute modifier.
	 *
	 * Allowed string values are the class constants:
	 *   - {@see WP_CSS_Attribute_Selector::MODIFIER_CASE_SENSITIVE}
	 *   - {@see WP_CSS_Attribute_Selector::MODIFIER_CASE_INSENSITIVE}
	 *
	 * @var string|null
	 */
	public $modifier;

	/**
	 * Constructor.
	 *
	 * @param string $name The attribute name.
	 * @param string|null $matcher The attribute matcher.
	 *        Must be one of the class MATCH_* constants or null.
	 * @param string|null $value The attribute value to match.
	 * @param string|null $modifier The attribute case modifier.
	 *        Must be one of the class MODIFIER_* constants or null.
	 */
	public function __construct( string $name, ?string $matcher = null, ?string $value = null, ?string $modifier = null ) {
		$this->name     = $name;
		$this->matcher  = $matcher;
		$this->value    = $value;
		$this->modifier = $modifier;
	}

	/**
	 * Determines if the processor's current position matches the selector.
	 *
	 * @param WP_HTML_Tag_Processor $processor The processor.
	 * @return bool True if the processor's current position matches the selector.
	 */
	public function matches( WP_HTML_Tag_Processor $processor ): bool {
		$att_value = $processor->get_attribute( $this->name );
		if ( null === $att_value ) {
			return false;
		}

		if ( null === $this->value ) {
			return true;
		}

		if ( true === $att_value ) {
			$att_value = '';
		}

		$case_insensitive = self::MODIFIER_CASE_INSENSITIVE === $this->modifier;

		switch ( $this->matcher ) {
			case self::MATCH_EXACT:
				return $case_insensitive
					? 0 === strcasecmp( $att_value, $this->value )
					: $att_value === $this->value;

			case self::MATCH_ONE_OF_EXACT:
				foreach ( $this->whitespace_delimited_list( $att_value ) as $val ) {
					if (
						$case_insensitive
							? 0 === strcasecmp( $val, $this->value )
							: $val === $this->value
					) {
						return true;
					}
				}
				return false;

			case self::MATCH_EXACT_OR_HYPHEN_PREFIXED:
				// Attempt the full match first
				if (
					$case_insensitive
						? 0 === strcasecmp( $att_value, $this->value )
						: $att_value === $this->value
				) {
					return true;
				}

				// Partial match
				if ( strlen( $att_value ) < strlen( $this->value ) + 1 ) {
					return false;
				}

				$starts_with = "{$this->value}-";
				return 0 === substr_compare( $att_value, $starts_with, 0, strlen( $starts_with ), $case_insensitive );

			case self::MATCH_PREFIXED_BY:
				return 0 === substr_compare( $att_value, $this->value, 0, strlen( $this->value ), $case_insensitive );

			case self::MATCH_SUFFIXED_BY:
				return 0 === substr_compare( $att_value, $this->value, -strlen( $this->value ), null, $case_insensitive );

			case self::MATCH_CONTAINS:
				return false !== (
					$case_insensitive
						? stripos( $att_value, $this->value )
						: strpos( $att_value, $this->value )
				);
		}
	}

	/**
	 * Splits a string into a list of whitespace delimited values.
	 *
	 * This is useful for the {@see WP_CSS_Attribute_Selector::MATCH_ONE_OF_EXACT} matcher.
	 *
	 * @param string $input
	 *
	 * @return Generator<string>
	 */
	private function whitespace_delimited_list( string $input ): Generator {
		// Start by skipping whitespace.
		$offset = strspn( $input, " \t\r\n\f" );

		while ( $offset < strlen( $input ) ) {
			// Find the byte length until the next boundary.
			$length = strcspn( $input, " \t\r\n\f", $offset );
			$value  = substr( $input, $offset, $length );

			// Move past trailing whitespace.
			$offset += $length + strspn( $input, " \t\r\n\f", $offset + $length );

			yield $value;
		}
	}
}
