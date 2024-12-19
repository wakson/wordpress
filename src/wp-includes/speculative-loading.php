<?php
/**
 * Speculative loading functions.
 *
 * @package WordPress
 * @subpackage Speculative Loading
 * @since 6.8.0
 */

/**
 * Returns the speculation rules configuration.
 *
 * @since 6.8.0
 *
 * @return array<string, string>|null Associative array with 'mode' and 'eagerness' keys, or null if speculative
 *                                    loading is disabled.
 */
function wp_get_speculation_rules_configuration(): ?array {
	$config = array(
		'mode'      => 'auto',
		'eagerness' => 'auto',
	);

	/**
	 * Filters the way that speculation rules are configured.
	 *
	 * The Speculation Rules API is a web API that allows to automatically prefetch or prerender certain URLs on the
	 * page, which can lead to near-instant page load times. This is also referred to as speculative loading.
	 *
	 * There are two aspects to the configuration:
	 * * The "mode" (whether to "prefetch" or "prerender" URLs).
	 * * The "eagerness" (whether to speculatively load URLs in an "eager", "moderate", or "conservative" way).
	 *
	 * By default, the speculation rules configuration is decided by WordPress Core ("auto"). This filter can be used
	 * to force a certain configuration, which could for instance load URLs more or less eagerly.
	 *
	 * @since 6.8.0
	 * @see https://developer.chrome.com/docs/web-platform/prerender-pages
	 *
	 * @param array<string, string>|null $config Associative array with 'mode' and 'eagerness' keys. The default value
	 *                                           for both of them is 'auto'. Other possible values for 'mode' are
	 *                                           'prefetch' and 'prerender'. Other possible values for 'eagerness' are
	 *                                           'eager', 'moderate', and 'conservative'. Alternatively, you may
	 *                                           return `null` to disable speculative loading entirely.
	 */
	$config = apply_filters( 'wp_speculation_rules_configuration', $config );

	// Allow the value `null` to indicate that speculative loading is disabled.
	if ( null === $config ) {
		return null;
	}

	// Sanitize the configuration and replace 'auto' with current defaults.
	$default_mode      = 'prefetch';
	$default_eagerness = 'conservative';
	if ( ! is_array( $config ) ) {
		return array(
			'mode'      => $default_mode,
			'eagerness' => $default_eagerness,
		);
	}
	if ( ! isset( $config['mode'] ) || 'auto' === $config['mode'] || ! wp_is_valid_speculation_rules_mode( $config['mode'] ) ) {
		$config['mode'] = $default_mode;
	}
	if ( ! isset( $config['eagerness'] ) || 'auto' === $config['eagerness'] || ! wp_is_valid_speculation_rules_eagerness( $config['eagerness'] ) ) {
		$config['eagerness'] = $default_eagerness;
	}

	return array(
		'mode'      => $config['mode'],
		'eagerness' => $config['eagerness'],
	);
}

/**
 * Checks whether the given speculation rules mode is valid.
 *
 * @since 6.8.0
 *
 * @param string $mode Speculation rules mode.
 * @return bool True if valid, false otherwise.
 */
function wp_is_valid_speculation_rules_mode( string $mode ): bool {
	static $mode_allowlist = array(
		'prefetch'  => true,
		'prerender' => true,
	);
	return isset( $mode_allowlist[ $mode ] );
}

/**
 * Checks whether the given speculation rules eagerness is valid.
 *
 * @since 6.8.0
 *
 * @param string $eagerness Speculation rules eagerness.
 * @return bool True if valid, false otherwise.
 */
function wp_is_valid_speculation_rules_eagerness( string $eagerness ): bool {
	static $eagerness_allowlist = array(
		'eager'        => true,
		'moderate'     => true,
		'conservative' => true,
	);
	return isset( $eagerness_allowlist[ $eagerness ] );
}

/**
 * Returns the full speculation rules data based on the given configuration.
 *
 * Plugins with features that rely on frontend URLs to exclude from prefetching or prerendering should use the
 * {@see 'wp_speculation_rules_href_exclude_paths'} filter to ensure those URL patterns are excluded.
 *
 * @since 6.8.0
 * @access private
 *
 * @param array<string, string> $configuration Associative array with 'mode' and 'eagerness' keys.
 * @return array<string, array<int, array<string, mixed>>> Associative array of speculation rules by type.
 */
function wp_get_speculation_rules( array $configuration ): array {
	// If the passed configuration is invalid, trigger a warning and fall back to the default configuration.
	if (
		! isset( $configuration['mode'] ) ||
		! wp_is_valid_speculation_rules_mode( $configuration['mode'] ) ||
		! isset( $configuration['eagerness'] ) ||
		! wp_is_valid_speculation_rules_eagerness( $configuration['eagerness'] )
	) {
		_doing_it_wrong(
			__FUNCTION__,
			esc_html(
				sprintf(
					/* translators: %s is $configuration */
					__( 'The %s parameter was provided with an invalid value.' ),
					'$configuration'
				)
			),
			'6.8.0'
		);
		$configuration = wp_get_speculation_rules_configuration();
	}

	$mode      = $configuration['mode'];
	$eagerness = $configuration['eagerness'];

	$prefixer = new WP_URL_Pattern_Prefixer();

	$base_href_exclude_paths = array(
		$prefixer->prefix_path_pattern( '/wp-login.php', 'site' ),
		$prefixer->prefix_path_pattern( '/wp-admin/*', 'site' ),
		$prefixer->prefix_path_pattern( '/*\\?*(^|&)_wpnonce=*', 'home' ),
		$prefixer->prefix_path_pattern( '/*', 'uploads' ),
		$prefixer->prefix_path_pattern( '/*', 'content' ),
		$prefixer->prefix_path_pattern( '/*', 'plugins' ),
		$prefixer->prefix_path_pattern( '/*', 'template' ),
		$prefixer->prefix_path_pattern( '/*', 'stylesheet' ),
	);

	/**
	 * Filters the paths for which speculative loading should be disabled.
	 *
	 * All paths should start in a forward slash, relative to the root document. The `*` can be used as a wildcard.
	 * If the WordPress site is in a subdirectory, the exclude paths will automatically be prefixed as necessary.
	 *
	 * Note that WordPress always excludes certain path patterns such as `/wp-login.php` and `/wp-admin/*`, and those
	 * cannot be modified using the filter.
	 *
	 * @since 6.8.0
	 *
	 * @param string[] $href_exclude_paths Additional path patterns to disable speculative loading for.
	 * @param string   $mode               Mode used to apply speculative loading. Either 'prefetch' or 'prerender'.
	 */
	$href_exclude_paths = (array) apply_filters( 'wp_speculation_rules_href_exclude_paths', array(), $mode );

	// Ensure that:
	// 1. There are no duplicates.
	// 2. The base paths cannot be removed.
	// 3. The array has sequential keys (i.e. array_is_list()).
	$href_exclude_paths = array_values(
		array_unique(
			array_merge(
				$base_href_exclude_paths,
				array_map(
					static function ( string $href_exclude_path ) use ( $prefixer ): string {
						return $prefixer->prefix_path_pattern( $href_exclude_path );
					},
					$href_exclude_paths
				)
			)
		)
	);

	$rules = array(
		array(
			'source'    => 'document',
			'where'     => array(
				'and' => array(
					// Include any URLs within the same site.
					array(
						'href_matches' => $prefixer->prefix_path_pattern( '/*' ),
					),
					// Except for excluded paths.
					array(
						'not' => array(
							'href_matches' => $href_exclude_paths,
						),
					),
					// Also exclude rel=nofollow links, as certain plugins use that on their links that perform an action.
					array(
						'not' => array(
							'selector_matches' => 'a[rel~="nofollow"]',
						),
					),
					// Last but not least, exclude links that are explicitly marked to opt out.
					array(
						'not' => array(
							'selector_matches' => ".no-{$mode}",
						),
					),
				),
			),
			'eagerness' => $eagerness,
		),
	);

	return array( $mode => $rules );
}

/**
 * Prints the speculation rules.
 *
 * For browsers that do not support speculation rules yet, the `script[type="speculationrules"]` tag will be ignored.
 *
 * @since 6.8.0
 * @access private
 */
function wp_print_speculation_rules(): void {
	$configuration = wp_get_speculation_rules_configuration();
	if ( null === $configuration ) {
		return;
	}

	wp_print_inline_script_tag(
		(string) wp_json_encode(
			wp_get_speculation_rules( $configuration )
		),
		array( 'type' => 'speculationrules' )
	);
}
