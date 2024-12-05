<?php

abstract class WP_CSS_HTML_Tag_Processor_Matcher {
	/**
	 * @return bool
	 */
	abstract public function matches( WP_HTML_Tag_Processor $processor ): bool;
}
