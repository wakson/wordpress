<?php

final class WP_CSS_ID_Selector implements WP_CSS_HTML_Tag_Processor_Matcher {
	/** @var string */
	public $ident;

	public function __construct( string $ident ) {
		$this->ident = $ident;
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
			? 0 === strcasecmp( $id, $this->ident )
			: $processor->get_attribute( 'id' ) === $this->ident;
	}
}
