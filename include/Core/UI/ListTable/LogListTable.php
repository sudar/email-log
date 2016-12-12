<?php namespace EmailLog\Core\UI\ListTable;

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
			// The code that goes before the table is here.
			echo '<span id = "el-pro-msg">';
			_e( 'More fields are available in Pro addon. ', 'email-log' );
			echo '<a href = "http://sudarmuthu.com/out/buy-email-log-more-fields-addon" style = "color:red">';
			_e( 'Buy Now', 'email-log' );
			echo '</a>';
			echo '</span>';
		}

		if ( 'bottom' == $which ) {
			// The code that goes after the table is here.
			echo '<p>&nbsp;</p>';
			echo '<p>&nbsp;</p>';

			echo '<p>';
			_e( 'The following are the list of pro addons that are currently available for purchase.', 'email-log' );
			echo '</p>';

			echo '<ul style="list-style:disc; padding-left:35px">';

			echo '<li>';
			echo '<strong>', __( 'Email Log - Resend Email', 'email-log' ), '</strong>', ' - ';
			echo __( 'Adds the ability to resend email from logs.', 'email-log' );
			echo ' <a href = "http://sudarmuthu.com/wordpress/email-log/pro-addons#resend-email-addon">', __( 'More Info', 'email-log' ), '</a>.';
			echo ' <a href = "http://sudarmuthu.com/out/buy-email-log-resend-email-addon">', __( 'Buy now', 'email-log' ), '</a>';
			echo '</li>';

			echo '<li>';
			echo '<strong>', __( 'Email Log - More fields', 'email-log' ), '</strong>', ' - ';
			echo __( 'Adds more fields (From, CC, BCC, Reply To, Attachment) to the logs page.', 'email-log' );
			echo ' <a href = "http://sudarmuthu.com/wordpress/email-log/pro-addons#more-fields-addon">', __( 'More Info', 'email-log' ), '</a>.';
			echo ' <a href = "http://sudarmuthu.com/out/buy-email-log-more-fields-addon">', __( 'Buy now', 'email-log' ), '</a>';
			echo '</li>';

			echo '<li>';
			echo '<strong>', __( 'Email Log - Forward Email', 'email-log' ), '</strong>', ' - ';
			echo __( 'This addon allows you to send a copy of all emails send from WordPress to another email address', 'email-log' );
			echo ' <a href = "http://sudarmuthu.com/wordpress/email-log/pro-addons#forward-email-addon">', __( 'More Info', 'email-log' ), '</a>.';
			echo ' <a href = "http://sudarmuthu.com/out/buy-email-log-forward-email-addon">', __( 'Buy now', 'email-log' ), '</a>';
			echo '</li>';

			echo '</ul>';
		}
	}

	/**
	 * Returns the list of column and title names.
	 *
	 * @see WP_List_Table::::single_row_columns()
	 *
	 * @return array An associative array containing column information: 'slugs'=>'Visible Titles'.
	 */
	public function get_columns() {
		$columns = array(
			'cb'        => '<input type="checkbox" />', // Render a checkbox instead of text.
			'sent_date' => __( 'Sent at', 'email-log' ),
			'to'        => __( 'To', 'email-log' ),
			'subject'   => __( 'Subject', 'email-log' ),
		);

		/**
		 * Filter the email log list table columns.
		 *
		 * @since 2.0
		 * @param array $columns Columns of email log list table.
		 */
		return apply_filters( 'email_log_manage_log_columns', $columns );
	}

	/**
	 * Returns the list of columns.
	 *
	 * @access protected
	 *
	 * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool).
	 */
	protected function get_sortable_columns() {
		$sortable_columns = array(
			'sent_date'   => array( 'sent_date', true ), //true means it's already sorted
			'to'          => array( 'to_email', false ),
			'subject'     => array( 'subject', false ),
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
		do_action( 'email_log_display_log_columns', $column_name, $item );
	}

	/**
	 * Display sent date column.
	 *
	 * @access protected
	 *
	 * @param  object $item Current item object.
	 * @return string       Markup to be displayed for the column.
	 */
	protected function column_sent_date( $item ) {
		$email_date = mysql2date(
			sprintf( __( '%s @ %s', 'email-log' ), get_option( 'date_format', 'F j, Y' ), get_option( 'time_format', 'g:i A' ) ),
			$item->sent_date
		);

		$actions = array();

		$content_ajax_url = add_query_arg(
			array(
				'action'    => 'display_email_message',
				'log_id'    => $item->id,
				'TB_iframe' => 'true',
				'width'     => '600',
				'height'    => '550',
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
				'action'                 => 'delete',
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
		 * @param array $actions List of actions.
		 * @param object $item The current log item.
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
	 * @return string
	 */
	protected function column_to( $item ) {
		return esc_html( $item->to_email );
	}

	/**
	 * Subject field.
	 *
	 * @access protected
	 *
	 * @param object $item
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
	 * Specify the list of bulk actions.
	 *
	 * @access protected
	 *
	 * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'.
	 */
	protected function get_bulk_actions() {
		$actions = array(
			'delete'     => __( 'Delete', 'email-log' ),
			'delete-all' => __( 'Delete All Logs', 'email-log' ),
		);
		return $actions;
	}

	/**
	 * Handles bulk actions.
	 *
	 * @access protected.
	 */
	protected function process_bulk_action() {
		if ( 'delete' === $this->current_action() ) {
			$this->page->delete_logs_by_id( $_GET[ $this->_args['singular'] ] );
		} elseif ( 'delete-all' === $this->current_action() ) {
			$this->page->delete_all_logs();
		}
	}

	/**
	 * Prepare data for display.
	 */
	public function prepare_items() {
		$this->_column_headers = $this->get_column_info();

		// Handle bulk actions.
		$this->process_bulk_action();

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
}
