<?php
/**
 * Blocks API: WP_Block class
 *
 * @package WordPress
 * @since 5.5.0
 */

/**
 * Class representing a parsed instance of a block.
 *
 * @since 5.5.0
 * @property array $attributes
 */
#[AllowDynamicProperties]
class WP_Block {

	/**
	 * Original parsed array representation of block.
	 *
	 * @since 5.5.0
	 * @var array
	 */
	public $parsed_block;

	/**
	 * Name of block.
	 *
	 * @example "core/paragraph"
	 *
	 * @since 5.5.0
	 * @var string
	 */
	public $name;

	/**
	 * Block type associated with the instance.
	 *
	 * @since 5.5.0
	 * @var WP_Block_Type
	 */
	public $block_type;

	/**
	 * Block context values.
	 *
	 * @since 5.5.0
	 * @var array
	 */
	public $context = array();

	/**
	 * All available context of the current hierarchy.
	 *
	 * @since 5.5.0
	 * @var array
	 * @access protected
	 */
	protected $available_context;

	/**
	 * Block type registry.
	 *
	 * @since 5.9.0
	 * @var WP_Block_Type_Registry
	 * @access protected
	 */
	protected $registry;

	/**
	 * List of inner blocks (of this same class)
	 *
	 * @since 5.5.0
	 * @var WP_Block_List
	 */
	public $inner_blocks = array();

	/**
	 * Resultant HTML from inside block comment delimiters after removing inner
	 * blocks.
	 *
	 * @example "...Just <!-- wp:test /--> testing..." -> "Just testing..."
	 *
	 * @since 5.5.0
	 * @var string
	 */
	public $inner_html = '';

	/**
	 * List of string fragments and null markers where inner blocks were found
	 *
	 * @example array(
	 *   'inner_html'    => 'BeforeInnerAfter',
	 *   'inner_blocks'  => array( block, block ),
	 *   'inner_content' => array( 'Before', null, 'Inner', null, 'After' ),
	 * )
	 *
	 * @since 5.5.0
	 * @var array
	 */
	public $inner_content = array();

	/**
	 * Constructor.
	 *
	 * Populates object properties from the provided block instance argument.
	 *
	 * The given array of context values will not necessarily be available on
	 * the instance itself, but is treated as the full set of values provided by
	 * the block's ancestry. This is assigned to the private `available_context`
	 * property. Only values which are configured to consumed by the block via
	 * its registered type will be assigned to the block's `context` property.
	 *
	 * @since 5.5.0
	 *
	 * @param array                  $block             Array of parsed block properties.
	 * @param array                  $available_context Optional array of ancestry context values.
	 * @param WP_Block_Type_Registry $registry          Optional block type registry.
	 */
	public function __construct( $block, $available_context = array(), $registry = null ) {
		$this->parsed_block = $block;
		$this->name         = $block['blockName'];

		if ( is_null( $registry ) ) {
			$registry = WP_Block_Type_Registry::get_instance();
		}

		$this->registry = $registry;

		$this->block_type = $registry->get_registered( $this->name );

		$this->available_context = $available_context;

		if ( ! empty( $this->block_type->uses_context ) ) {
			foreach ( $this->block_type->uses_context as $context_name ) {
				if ( array_key_exists( $context_name, $this->available_context ) ) {
					$this->context[ $context_name ] = $this->available_context[ $context_name ];
				}
			}
		}

		if ( ! empty( $block['innerBlocks'] ) ) {
			$child_context = $this->available_context;

			if ( ! empty( $this->block_type->provides_context ) ) {
				foreach ( $this->block_type->provides_context as $context_name => $attribute_name ) {
					if ( array_key_exists( $attribute_name, $this->attributes ) ) {
						$child_context[ $context_name ] = $this->attributes[ $attribute_name ];
					}
				}
			}

			$this->inner_blocks = new WP_Block_List( $block['innerBlocks'], $child_context, $registry );
		}

		if ( ! empty( $block['innerHTML'] ) ) {
			$this->inner_html = $block['innerHTML'];
		}

		if ( ! empty( $block['innerContent'] ) ) {
			$this->inner_content = $block['innerContent'];
		}
	}

	/**
	 * Returns a value from an inaccessible property.
	 *
	 * This is used to lazily initialize the `attributes` property of a block,
	 * such that it is only prepared with default attributes at the time that
	 * the property is accessed. For all other inaccessible properties, a `null`
	 * value is returned.
	 *
	 * @since 5.5.0
	 *
	 * @param string $name Property name.
	 * @return array|null Prepared attributes, or null.
	 */
	public function __get( $name ) {
		if ( 'attributes' === $name ) {
			$this->attributes = isset( $this->parsed_block['attrs'] ) ?
				$this->parsed_block['attrs'] :
				array();

			if ( ! is_null( $this->block_type ) ) {
				$this->attributes = $this->block_type->prepare_attributes_for_render( $this->attributes );
			}

			return $this->attributes;
		}

		return null;
	}

	/**
	 * Processes the block bindings in block's attributes.
	 *
	 * A block might contain bindings in its attributes. Bindings are mappings
	 * between an attribute of the block and a source. A "source" is a function
	 * registered with `register_block_bindings_source()` that defines how to
	 * retrieve a value from outside the block, e.g. from post meta.
	 *
	 * This function will process those bindings and replace the HTML with the value of the binding.
	 * The value is retrieved from the source of the binding.
	 *
	 * ### Example
	 *
	 * The "bindings" property for an Image block might look like this:
	 *
	 * ```json
	 * {
	 *   "metadata": {
	 *     "bindings": {
	 *       "title": {
	 *         "source": "post_meta",
	 *         "args": { "key": "text_custom_field" }
	 *       },
	 *       "url": {
	 *         "source": "post_meta",
	 *         "args": { "key": "url_custom_field" }
	 *       }
	 *     }
	 *   }
	 * }
	 * ```
	 *
	 * The above example will replace the `title` and `url` attributes of the Image
	 * block with the values of the `text_custom_field` and `url_custom_field` post meta.
	 *
	 * @access private
	 * @since 6.5.0
	 *
	 * @param string   $block_content Block content.
	 * @param array    $block The full block, including name and attributes.
	 */
	private function process_block_bindings( $block_content ) {
		$block = $this->parsed_block;

		// Allowed blocks that support block bindings.
		// TODO: Look for a mechanism to opt-in for this. Maybe adding a property to block attributes?
		$allowed_blocks = array(
			'core/paragraph' => array( 'content' ),
			'core/heading'   => array( 'content' ),
			'core/image'     => array( 'url', 'title', 'alt' ),
			'core/button'    => array( 'url', 'text' ),
		);

		// If the block doesn't have the bindings property or isn't one of the allowed block types, return.
		if ( ! isset( $block['attrs']['metadata']['bindings'] ) || ! isset( $allowed_blocks[ $this->name ] ) ) {
			return $block_content;
		}

		$block_bindings_sources = get_all_registered_block_bindings_sources();
		$modified_block_content = $block_content;
		foreach ( $block['attrs']['metadata']['bindings'] as $binding_attribute => $binding_source ) {
			// If the attribute is not in the list, process next attribute.
			if ( ! in_array( $binding_attribute, $allowed_blocks[ $this->name ], true ) ) {
				continue;
			}
			// If no source is provided, or that source is not registered, process next attribute.
			if ( ! isset( $binding_source['source'] ) || ! is_string( $binding_source['source'] ) || ! isset( $block_bindings_sources[ $binding_source['source'] ] ) ) {
				continue;
			}

			$source_callback = $block_bindings_sources[ $binding_source['source'] ]['get_value_callback'];
			// Get the value based on the source.
			if ( ! isset( $binding_source['args'] ) ) {
				$source_args = array();
			} else {
				$source_args = $binding_source['args'];
			}
			$source_value = call_user_func_array( $source_callback, array( $source_args, $this, $binding_attribute ) );
			// If the value is null, process next attribute.
			if ( is_null( $source_value ) ) {
				continue;
			}

			// Process the HTML based on the block and the attribute.
			$modified_block_content = $this->replace_html( $modified_block_content, $this->name, $binding_attribute, $source_value );
		}
		return $modified_block_content;
	}

	/**
	 * Depending on the block attributes, replace the HTML based on the value returned by the source.
	 *
	 * @since 6.5.0
	 *
	 * @param string $block_content Block content.
	 * @param string $block_name The name of the block to process.
	 * @param string $block_attr The attribute of the block we want to process.
	 * @param string $source_value The value used to replace the HTML.
	 */
	private function replace_html( string $block_content, string $block_name, string $block_attr, string $source_value ) {
		$block_type = $this->block_type;
		if ( null === $block_type || ! isset( $block_type->attributes[ $block_attr ] ) ) {
			return $block_content;
		}

		// Depending on the attribute source, the processing will be different.
		switch ( $block_type->attributes[ $block_attr ]['source'] ) {
			case 'html':
			case 'rich-text':
				$block_reader = new WP_HTML_Tag_Processor( $block_content );

				// TODO: Support for CSS selectors whenever they are ready in the HTML API.
				// In the meantime, support comma-separated selectors by exploding them into an array.
				$selectors = explode( ',', $block_type->attributes[ $block_attr ]['selector'] );
				// Add a bookmark to the first tag to be able to iterate over the selectors.
				$block_reader->next_tag();
				$block_reader->set_bookmark( 'iterate-selectors' );

				// TODO: This shouldn't be needed when the `set_inner_html` function is ready.
				// Store the parent tag and its attributes to be able to restore them later in the button.
				// The button block has a wrapper while the paragraph and heading blocks don't.
				if ( 'core/button' === $block_name ) {
					$button_wrapper                 = $block_reader->get_tag();
					$button_wrapper_attribute_names = $block_reader->get_attribute_names_with_prefix( '' );
					$button_wrapper_attrs           = array();
					foreach ( $button_wrapper_attribute_names as $name ) {
						$button_wrapper_attrs[ $name ] = $block_reader->get_attribute( $name );
					}
				}

				foreach ( $selectors as $selector ) {
					// If the parent tag, or any of its children, matches the selector, replace the HTML.
					if ( strcasecmp( $block_reader->get_tag( $selector ), $selector ) === 0 || $block_reader->next_tag(
						array(
							'tag_name' => $selector,
						)
					) ) {
						$block_reader->release_bookmark( 'iterate-selectors' );

						// TODO: Use `set_inner_html` method whenever it's ready in the HTML API.
						// Until then, it is hardcoded for the paragraph, heading, and button blocks.
						// Store the tag and its attributes to be able to restore them later.
						$selector_attribute_names = $block_reader->get_attribute_names_with_prefix( '' );
						$selector_attrs           = array();
						foreach ( $selector_attribute_names as $name ) {
							$selector_attrs[ $name ] = $block_reader->get_attribute( $name );
						}
						$selector_markup = "<$selector>" . wp_kses_post( $source_value ) . "</$selector>";
						$amended_content = new WP_HTML_Tag_Processor( $selector_markup );
						$amended_content->next_tag();
						foreach ( $selector_attrs as $attribute_key => $attribute_value ) {
							$amended_content->set_attribute( $attribute_key, $attribute_value );
						}
						if ( 'core/paragraph' === $block_name || 'core/heading' === $block_name ) {
							return $amended_content->get_updated_html();
						}
						if ( 'core/button' === $block_name ) {
							$button_markup  = "<$button_wrapper>{$amended_content->get_updated_html()}</$button_wrapper>";
							$amended_button = new WP_HTML_Tag_Processor( $button_markup );
							$amended_button->next_tag();
							foreach ( $button_wrapper_attrs as $attribute_key => $attribute_value ) {
								$amended_button->set_attribute( $attribute_key, $attribute_value );
							}
							return $amended_button->get_updated_html();
						}
					} else {
						$block_reader->seek( 'iterate-selectors' );
					}
				}
				$block_reader->release_bookmark( 'iterate-selectors' );
				return $block_content;

			case 'attribute':
				$amended_content = new WP_HTML_Tag_Processor( $block_content );
				if ( ! $amended_content->next_tag(
					array(
						// TODO: build the query from CSS selector.
						'tag_name' => $block_type->attributes[ $block_attr ]['selector'],
					)
				) ) {
					return $block_content;
				}
				$amended_content->set_attribute( $block_type->attributes[ $block_attr ]['attribute'], esc_attr( $source_value ) );
				return $amended_content->get_updated_html();
				break;

			default:
				return $block_content;
				break;
		}
		return;
	}


	/**
	 * Generates the render output for the block.
	 *
	 * @since 5.5.0
	 *
	 * @global WP_Post $post Global post object.
	 *
	 * @param array $options {
	 *     Optional options object.
	 *
	 *     @type bool $dynamic Defaults to 'true'. Optionally set to false to avoid using the block's render_callback.
	 * }
	 * @return string Rendered block output.
	 */
	public function render( $options = array() ) {
		global $post;
		$options = wp_parse_args(
			$options,
			array(
				'dynamic' => true,
			)
		);

		$is_dynamic    = $options['dynamic'] && $this->name && null !== $this->block_type && $this->block_type->is_dynamic();
		$block_content = '';

		if ( ! $options['dynamic'] || empty( $this->block_type->skip_inner_blocks ) ) {
			$index = 0;

			foreach ( $this->inner_content as $chunk ) {
				if ( is_string( $chunk ) ) {
					$block_content .= $chunk;
				} else {
					$inner_block  = $this->inner_blocks[ $index ];
					$parent_block = $this;

					/** This filter is documented in wp-includes/blocks.php */
					$pre_render = apply_filters( 'pre_render_block', null, $inner_block->parsed_block, $parent_block );

					if ( ! is_null( $pre_render ) ) {
						$block_content .= $pre_render;
					} else {
						$source_block = $inner_block->parsed_block;

						/** This filter is documented in wp-includes/blocks.php */
						$inner_block->parsed_block = apply_filters( 'render_block_data', $inner_block->parsed_block, $source_block, $parent_block );

						/** This filter is documented in wp-includes/blocks.php */
						$inner_block->context = apply_filters( 'render_block_context', $inner_block->context, $inner_block->parsed_block, $parent_block );

						$block_content .= $inner_block->render();
					}

					++$index;
				}
			}
		}

		if ( $is_dynamic ) {
			$global_post = $post;
			$parent      = WP_Block_Supports::$block_to_render;

			WP_Block_Supports::$block_to_render = $this->parsed_block;

			$block_content = (string) call_user_func( $this->block_type->render_callback, $this->attributes, $block_content, $this );

			WP_Block_Supports::$block_to_render = $parent;

			$post = $global_post;
		}

		if ( ( ! empty( $this->block_type->script_handles ) ) ) {
			foreach ( $this->block_type->script_handles as $script_handle ) {
				wp_enqueue_script( $script_handle );
			}
		}

		if ( ! empty( $this->block_type->view_script_handles ) ) {
			foreach ( $this->block_type->view_script_handles as $view_script_handle ) {
				wp_enqueue_script( $view_script_handle );
			}
		}

		if ( ( ! empty( $this->block_type->style_handles ) ) ) {
			foreach ( $this->block_type->style_handles as $style_handle ) {
				wp_enqueue_style( $style_handle );
			}
		}

		if ( ( ! empty( $this->block_type->view_style_handles ) ) ) {
			foreach ( $this->block_type->view_style_handles as $view_style_handle ) {
				wp_enqueue_style( $view_style_handle );
			}
		}

		// Process the block bindings for this block, if any are registered. This
		// will replace the block content with the value from a registered binding source.
		$block_content = $this->process_block_bindings( $block_content );

		/**
		 * Filters the content of a single block.
		 *
		 * @since 5.0.0
		 * @since 5.9.0 The `$instance` parameter was added.
		 *
		 * @param string   $block_content The block content.
		 * @param array    $block         The full block, including name and attributes.
		 * @param WP_Block $instance      The block instance.
		 */
		$block_content = apply_filters( 'render_block', $block_content, $this->parsed_block, $this );

		/**
		 * Filters the content of a single block.
		 *
		 * The dynamic portion of the hook name, `$name`, refers to
		 * the block name, e.g. "core/paragraph".
		 *
		 * @since 5.7.0
		 * @since 5.9.0 The `$instance` parameter was added.
		 *
		 * @param string   $block_content The block content.
		 * @param array    $block         The full block, including name and attributes.
		 * @param WP_Block $instance      The block instance.
		 */
		$block_content = apply_filters( "render_block_{$this->name}", $block_content, $this->parsed_block, $this );

		return $block_content;
	}
}
