<?php

abstract class WP_CSS_HTML_Processor_Matcher {
	/**
	 * @return bool
	 */
	abstract public function matches( WP_HTML_Processor $processor ): bool;
}
