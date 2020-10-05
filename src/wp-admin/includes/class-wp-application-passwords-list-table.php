<?php
/**
 * List Table API: WP_Application_Passwords_List_Table class
 *
 * @package WordPress
 * @subpackage Administration
 * @since ?.?.0
 */

/**
 * Class for displaying the list of application password items.
 *
 * @since ?.?.0
 * @access private
 *
 * @see WP_List_Table
 */
class WP_Application_Passwords_List_Table extends WP_List_Table {

	/**
	 * Get a list of columns.
	 *
	 * @since ?.?.0
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'name'      => __( 'Name' ),
			'created'   => __( 'Created' ),
			'last_used' => __( 'Last Used' ),
			'last_ip'   => __( 'Last IP' ),
			'revoke'    => __( 'Revoke' ),
		);
	}

	/**
	 * Prepares the list of items for displaying.
	 *
	 * @since ?.?.0
	 */
	public function prepare_items() {
		global $user_id;
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = array();
		$primary  = 'name';

		$this->_column_headers = array( $columns, $hidden, $sortable, $primary );
		$this->items           = array_reverse( WP_Application_Passwords::get_user_application_passwords( $user_id ) );
	}

	/**
	 * Generates content for a single row of the table
	 *
	 * @since ?.?.0
	 *
	 * @param array  $item        The current item.
	 * @param string $column_name The current column name.
	 * @return string
	 */
	protected function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'name':
				return esc_html( $item['name'] );
			case 'created':
				if ( empty( $item['created'] ) ) {
					return '&mdash;';
				}
				return gmdate( get_option( 'date_format', 'r' ), $item['created'] );
			case 'last_used':
				if ( empty( $item['last_used'] ) ) {
					return '&mdash;';
				}
				return gmdate( get_option( 'date_format', 'r' ), $item['last_used'] );
			case 'last_ip':
				if ( empty( $item['last_ip'] ) ) {
					return '&mdash;';
				}
				return $item['last_ip'];
			case 'revoke':
				return get_submit_button( __( 'Revoke' ), 'delete', 'revoke-application-password', false );
			default:
				return '';
		}
	}

	/**
	 * Generates custom table navigation to prevent conflicting nonces.
	 *
	 * @since ?.?.0
	 *
	 * @param string $which The location of the bulk actions: 'top' or 'bottom'.
	 */
	protected function display_tablenav( $which ) {
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<?php if ( 'bottom' === $which ) : ?>
			<div class="alignright">
				<?php submit_button( __( 'Revoke all application passwords' ), 'delete', 'revoke-all-application-passwords', false ); ?>
			</div>
			<?php endif; ?>

			<div class="alignleft actions bulkactions">
				<?php $this->bulk_actions( $which ); ?>
			</div>
			<?php
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>

			<br class="clear" />
		</div>
		<?php
	}

	/**
	 * Generates content for a single row of the table.
	 *
	 * @since ?.?.0
	 *
	 * @param object $item The current item.
	 */
	public function single_row( $item ) {
		echo '<tr data-uuid="' . esc_attr( $item['uuid'] ) . '">';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 * Gets the name of the default primary column.
	 *
	 * @since ?.?.0
	 *
	 * @return string Name of the default primary column, in this case, 'name'.
	 */
	protected function get_default_primary_column_name() {
		return 'name';
	}
}
