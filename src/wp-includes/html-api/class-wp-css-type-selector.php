<?php

final class WP_CSS_Type_Selector implements WP_CSS_HTML_Tag_Processor_Matcher {
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
	 * @param string $tag_name
	 * @return bool
	 */
	public function matches_tag( string $tag_name ): bool {
		if ( '*' === $this->ident ) {
			return true;
		}
		return 0 === strcasecmp( $tag_name, $this->ident );
	}

	/**
	 * @var string
	 *
	 * The type identifier string or '*'.
	 */
	public $ident;

	public function __construct( string $ident ) {
		$this->ident = $ident;
	}
}
