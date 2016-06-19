<?php
/**
 * Table to display Email Logs.
 *
 * Based on Custom List Table Example by Matt Van Andel.
 *
 * @author  Sudar
 * @package Email Log
 */
class Email_Log_List_Table extends WP_List_Table {

	/**
	 * Set up a constructor that references the parent constructor.
	 *
	 * We use the parent reference to set some default configs.
	 */
	public function __construct() {
		parent::__construct( array(
			'singular'  => 'email-log',     //singular name of the listed records
			'plural'    => 'email-logs',    //plural name of the listed records
			'ajax'      => false,           //does this table support ajax?
		) );
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
			'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
			'sent_date' => __( 'Sent at', 'email-log' ),
			'to'        => __( 'To', 'email-log' ),
			'subject'   => __( 'Subject', 'email-log' ),
		);

		return apply_filters( EmailLog::HOOK_LOG_COLUMNS, $columns );
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
	 * @param object $item
	 * @param string $column_name
	 */
	protected function column_default( $item, $column_name ) {
		do_action( EmailLog::HOOK_LOG_DISPLAY_COLUMNS, $column_name, $item );
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
				'action'    => 'display_content',
				'email_id'  => $item->id,
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
				'page'                           => $_REQUEST['page'],
				'action'                         => 'delete',
				$this->_args['singular']         => $item->id,
				EmailLog::DELETE_LOG_NONCE_FIELD => wp_create_nonce( EmailLog::DELETE_LOG_ACTION ),
			)
		);

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
	 * @see $this->prepare_items()
	 */
	public function process_bulk_action() {
		global $wpdb;
		global $EmailLog; //@codingStandardsIgnoreLine

		if ( 'delete' === $this->current_action() ) {
			// Delete a list of logs by id.

			$nonce = $_REQUEST[ EmailLog::DELETE_LOG_NONCE_FIELD ];
			if ( wp_verify_nonce( $nonce, EmailLog::DELETE_LOG_ACTION ) ) {

				$ids = $_GET[ $this->_args['singular'] ];

				if ( is_array( $ids ) ) {
					$selected_ids = implode( ',', $ids );
				} else {
					$selected_ids = $ids;
				}

				// Can't use wpdb->prepare for the below query. If used it results in this bug
				// https://github.com/sudar/email-log/issues/13

				$selected_ids = esc_sql( $selected_ids );

				$table_name = $wpdb->prefix . EmailLog::TABLE_NAME;
				$EmailLog->logs_deleted = $wpdb->query( "DELETE FROM $table_name where id IN ( $selected_ids )" ); //@codingStandardsIgnoreLine
			} else {
				wp_die( 'Cheating, Huh? ' );
			}
		} elseif ( 'delete-all' === $this->current_action() ) {
			// Delete all logs.
			$nonce = $_REQUEST[ EmailLog::DELETE_LOG_NONCE_FIELD ];
			if ( wp_verify_nonce( $nonce, EmailLog::DELETE_LOG_ACTION ) ) {
				$table_name = $wpdb->prefix . EmailLog::TABLE_NAME;
				$EmailLog->logs_deleted = $wpdb->query( "DELETE FROM $table_name" ); //@codingStandardsIgnoreLine
			} else {
				wp_die( 'Cheating, Huh? ' );
			}
		}
	}

	/**
	 * Prepare data for display.
	 */
	public function prepare_items() {
		global $wpdb;

		$table_name = $wpdb->prefix . EmailLog::TABLE_NAME;
		$this->_column_headers = $this->get_column_info();

		// Handle bulk actions.
		$this->process_bulk_action();

		// Get current page number.
		$current_page = $this->get_pagenum();

		$query = 'SELECT * FROM ' . $table_name;
		$count_query = 'SELECT count(*) FROM ' . $table_name;
		$query_cond = '';

		if ( isset( $_GET['s'] ) ) {
			$search_term = trim( esc_sql( $_GET['s'] ) );
			$query_cond .= " WHERE to_email LIKE '%$search_term%' OR subject LIKE '%$search_term%' ";
		}

		// Ordering parameters.
		$orderby = ! empty( $_GET['orderby'] ) ? esc_sql( $_GET['orderby'] ) : 'sent_date';
		$order   = ! empty( $_GET['order'] ) ? esc_sql( $_GET['order'] ) : 'DESC';

		if ( ! empty( $orderby ) & ! empty( $order ) ) {
			$query_cond .= ' ORDER BY ' . $orderby . ' ' . $order;
		}

		// Find total number of items.
		$count_query = $count_query . $query_cond;
		$total_items = $wpdb->get_var( $count_query );

		// Adjust the query to take pagination into account.
		$per_page = EmailLog::get_per_page();
		if ( ! empty( $current_page ) && ! empty( $per_page ) ) {
			$offset = ( $current_page - 1 ) * $per_page;
			$query_cond .= ' LIMIT ' . (int) $offset . ',' . (int) $per_page;
		}

		// Fetch the items.
		$query = $query . $query_cond;
		$this->items = $wpdb->get_results( $query );

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
?>
