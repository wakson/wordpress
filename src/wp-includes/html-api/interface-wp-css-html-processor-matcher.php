<?php

interface WP_CSS_HTML_Processor_Matcher {
	/**
	 * Determines if the processor's current position matches the selector.
	 *
	 * @param WP_HTML_Processor $processor The processor.
	 * @return bool True if the processor's current position matches the selector.
	 */
	public function matches( WP_HTML_Processor $processor ): bool;
}
