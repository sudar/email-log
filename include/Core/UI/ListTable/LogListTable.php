<?php namespace EmailLog\Core\UI\ListTable;

use EmailLog\Util;
use function EmailLog\Util\get_display_format_for_log_time;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . WPINC . '/class-wp-list-table.php';
}

/**
 * Table to display Email Logs.
 *
 * Based on Custom List Table Example by Matt Van Andel.
 */
class LogListTable extends \WP_List_Table {
	/**
	 * @var object The page where this table is rendered.
	 *
	 * @since 2.0
	 */
	protected $page;

	/**
	 * Set up a constructor that references the parent constructor.
	 *
	 * We use the parent reference to set some default configs.
	 *
	 * @param \EmailLog\Core\UI\Page\LogListPage $page
	 * @param mixed                              $args
	 */
	public function __construct( $page, $args = array() ) {
		$this->page = $page;

		$args = wp_parse_args( $args, array(
			'singular' => 'email-log',     // singular name of the listed records
			'plural'   => 'email-logs',    // plural name of the listed records
			'ajax'     => false,           // does this table support ajax?
			'screen'   => $this->page->get_screen(),
		) );

		parent::__construct( $args );
	}

	/**
	 * Adds extra markup in the toolbars before or after the list.
	 *
	 * @access protected
	 *
	 * @param string $which Add the markup after (bottom) or before (top) the list.
	 */
	protected function extra_tablenav( $which ) {
		if ( 'top' == $which ) {
			/**
			 * Triggered before the logs list table is displayed.
			 *
			 * @since 2.2.5
			 * @since 2.4.0 Added $total_logs parameter
			 *
			 * @param int $total_logs Total number of logs.
			 */
			do_action( 'el_before_logs_list_table', $this->get_pagination_arg( 'total_items' ) );
		}
	}

	/**
	 * Returns the list of column and title names.
	 *
	 * @since 2.3.0 Retrieve Column labels using Utility methods.
	 * @since 2.3.2 Added `result` column.
	 * @since 2.4.0 Added `sent_status` column.
	 * @see WP_List_Table::single_row_columns()
	 *
	 * @uses \EmailLog\Util\get_column_label()
	 *
	 * @return array An associative array containing column information: 'slugs'=>'Visible Titles'.
	 */
	public function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
		);

		foreach ( array( 'sent_date', 'result', 'to_email', 'subject' ) as $column ) {
			$columns[ $column ] = Util\get_column_label( $column );
		}

		/**
		 * Filter the email log list table columns.
		 *
		 * @since 2.0.0
		 *
		 * @param array $columns Columns of email log list table.
		 */
		return apply_filters( 'el_manage_log_columns', $columns );
	}

	/**
	 * Returns the list of columns.
	 *
	 * @access protected
	 *
	 * @return array<string,array<boolean|string>> An associative array containing all the columns
	 *                                             that should be sortable: 'slugs'=>array('data_values',bool).
	 */
	protected function get_sortable_columns() {
		$sortable_columns = array(
			'sent_date' => array( 'sent_date', true ), // true means it's already sorted.
			'to_email'  => array( 'to_email', false ),
			'subject'   => array( 'subject', false ),
		);

		return $sortable_columns;
	}

	/**
	 * Returns value for default columns.
	 *
	 * @access protected
	 *
	 * @param object $item        Data object.
	 * @param string $column_name Column Name.
	 */
	protected function column_default( $item, $column_name ) {
		/**
		 * Display Email Log list table columns.
		 *
		 * @since 2.0
		 *
		 * @param string $column_name Column Name.
		 * @param object $item        Data object.
		 */
		do_action( 'el_display_log_columns', $column_name, $item );
	}

	/**
	 * Display sent date column.
	 *
	 * @access protected
	 *
	 * @param object $item Current item object.
	 *
	 * @return string Markup to be displayed for the column.
	 */
	protected function column_sent_date( $item ) {
		$email_date = mysql2date(
			sprintf(
				/* translators: 1 Date of the log, 2 Time of the log */
				__( '%1$s @ %2$s', 'email-log' ),
				get_option( 'date_format', 'F j, Y' ),
				get_display_format_for_log_time()
			),
			$item->sent_date
		);

		$actions = array();

		$content_ajax_url = add_query_arg(
			array(
				'action' => 'el-log-list-view-message',
				'log_id' => $item->id,
				'width'  => '800',
				'height' => '550',
			),
			'admin-ajax.php'
		);

		$actions['view-content'] = sprintf( '<a href="%1$s" class="thickbox" title="%2$s">%3$s</a>',
			esc_url( $content_ajax_url ),
			__( 'Email Content', 'email-log' ),
			__( 'View Content', 'email-log' )
		);

		$delete_url = add_query_arg(
			array(
				'page'                   => $_REQUEST['page'],
				'action'                 => 'el-log-list-delete',
				$this->_args['singular'] => $item->id,
			)
		);
		$delete_url = add_query_arg( $this->page->get_nonce_args(), $delete_url );

		$actions['delete'] = sprintf( '<a href="%s">%s</a>',
			esc_url( $delete_url ),
			__( 'Delete', 'email-log' )
		);

		/**
		 * This filter can be used to modify the list of row actions that are displayed.
		 *
		 * @since 1.8
		 *
		 * @param array  $actions List of actions.
		 * @param object $item    The current log item.
		 */
		$actions = apply_filters( 'el_row_actions', $actions, $item );

		return sprintf( '%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
			/*$1%s*/ $email_date,
			/*$2%s*/ $item->id,
			/*$3%s*/ $this->row_actions( $actions )
		);
	}

	/**
	 * To field.
	 *
	 * @access protected
	 *
	 * @param object $item
	 *
	 * @return string
	 */
	protected function column_to_email( $item ) {
		/**
		 * Filters the `To` field before outputting on the table.
		 *
		 * @since 2.3.0
		 *
		 * @param string $email `To` field
		 */
		$email = apply_filters( 'el_log_list_column_to_email', esc_html( $item->to_email ) );

		return $email;
	}

	/**
	 * Subject field.
	 *
	 * @access protected
	 *
	 * @param object $item
	 *
	 * @return string
	 */
	protected function column_subject( $item ) {
		return esc_html( $item->subject );
	}

	/**
	 * Markup for action column.
	 *
	 * @access protected
	 *
	 * @param object $item
	 *
	 * @return string
	 */
	protected function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ $this->_args['singular'],
			/*$2%s*/ $item->id
		);
	}

	/**
	 * Markup for Status column.
	 *
	 * @since 2.3.2
	 * @since 2.4.0 Output the error message as tooltip.
	 *
	 * @param object $item Email Log item.
	 *
	 * @return string Column markup.
	 */
	protected function column_result( $item ) {
		// For older records that does not have value in the result column,
		// $item->result will be null.
		if ( is_null( $item->result ) ) {
			return '';
		}

		$icon = \EmailLog\Util\get_failure_icon();
		if ( $item->result ) {
			$icon = \EmailLog\Util\get_success_icon();
		}

		if ( ! isset( $item->error_message ) ) {
			return $icon;
		}

		return sprintf(
			'<span class="%3$s" title="%2$s">%1$s</span>',
			$icon,
			esc_attr( $item->error_message ),
			'el-help'
		);
	}

	/**
	 * Specify the list of bulk actions.
	 *
	 * @access protected
	 *
	 * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'.
	 */
	protected function get_bulk_actions() {
		$actions = array(
			'el-log-list-delete'     => __( 'Delete', 'email-log' ),
			'el-log-list-delete-all' => __( 'Delete All Logs', 'email-log' ),
		);
		$actions = apply_filters( 'el_bulk_actions', $actions );

		return $actions;
	}

	/**
	 * Prepare data for display.
	 */
	public function prepare_items() {
		$this->_column_headers = $this->get_column_info();

		// Get current page number.
		$current_page_no = $this->get_pagenum();
		$per_page        = $this->page->get_per_page();

		list( $items, $total_items ) = $this->page->get_table_manager()->fetch_log_items( $_GET, $per_page, $current_page_no );

		$this->items = $items;

		// Register pagination options & calculations.
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page ),
		) );
	}

	/**
	 * Displays default message when no items are found.
	 */
	public function no_items() {
		_e( 'Your email log is empty', 'email-log' );
	}

	/**
	 * Displays the search box.
	 *
	 * @since 2.0
	 *
	 * @param string $text     The 'submit' button label.
	 * @param string $input_id ID attribute value for the search input field.
	 */
	public function search_box( $text, $input_id ) {
		$input_text_id  = $input_id . '-search-input';
		$input_date_id  = $input_id . '-search-date-input';
		$input_date_val = ( ! empty( $_REQUEST['d'] ) ) ? sanitize_text_field( $_REQUEST['d'] ) : '';

		if ( ! empty( $_REQUEST['orderby'] ) )
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		if ( ! empty( $_REQUEST['order'] ) )
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		if ( ! empty( $_REQUEST['post_mime_type'] ) )
			echo '<input type="hidden" name="post_mime_type" value="' . esc_attr( $_REQUEST['post_mime_type'] ) . '" />';
		if ( ! empty( $_REQUEST['detached'] ) )
			echo '<input type="hidden" name="detached" value="' . esc_attr( $_REQUEST['detached'] ) . '" />';
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_date_id ); ?>" name="d" value="<?php echo esc_attr( $input_date_val ); ?>" placeholder="<?php _e( 'Search by date', 'email-log' ); ?>" />
			<input type="search" id="<?php echo esc_attr( $input_text_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" placeholder="<?php _e( 'Search by term', 'email-log' ); ?>" />
			<?php submit_button( $text, '', '', false, array( 'id' => 'search-submit' ) ); ?>
		</p>
		<?php
	}
}
