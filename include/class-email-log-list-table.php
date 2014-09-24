<?php
/**
 * Table to display Email Logs
 *
 * Based on Custom List Table Example by Matt Van Andel
 *
 * @package Email Log
 * @author  Sudar
 */
class Email_Log_List_Table extends WP_List_Table {

    /**
     * Set up a constructor that references the parent constructor. We
     * use the parent reference to set some default configs.
     */
    function __construct(){
        global $status, $page;

        //Set parent defaults
        parent::__construct( array(
            'singular'  => 'email-log',     //singular name of the listed records
            'plural'    => 'email-logs',    //plural name of the listed records
            'ajax'      => false            //does this table support ajax?
        ) );
    }

	/**
	 * Add extra markup in the toolbars before or after the list
     *
	 * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
	 */
	function extra_tablenav( $which ) {
		if ( $which == "top" ){
			//The code that goes before the table is here
            echo '<span id = "el-pro-msg">';
            _e('More fields are available in Pro addon. ', 'email-log');
            echo '<a href = "http://sudarmuthu.com/out/buy-email-log-more-fields-addon" style = "color:red">';
            _e('Buy Now', 'email-log');
            echo '</a>';
            echo '</span>';
		}

		if ( $which == "bottom" ){
			//The code that goes after the table is there
            echo '<p>&nbsp;</p>';
            echo '<p>&nbsp;</p>';

            echo '<p>';
            _e('The following are the list of pro addons that are currently available for purchase.', 'email-log');
            echo '</p>';

            echo '<ul style="list-style:disc; padding-left:35px">';

            echo '<li>';
            echo '<strong>', __('Email Log - Forward Email', 'email-log'), '</strong>', ' - ';
            echo __('This addon allows you to send a copy of all emails send from WordPress to another email address', 'email-log');
            echo ' <a href = "http://sudarmuthu.com/wordpress/email-log/pro-addons#forward-email-addon">', __('More Info', 'email-log'), '</a>.';
            echo ' <a href = "http://sudarmuthu.com/out/buy-email-log-forward-email-addon">', __('Buy now', 'email-log'), '</a>';
            echo '</li>';

            echo '<li>';
            echo '<strong>', __('Email Log - More fields', 'email-log'), '</strong>', ' - ';
            echo __('Adds more fields (From, CC, BCC, Reply To, Attachment) to the logs page.', 'email-log');
            echo ' <a href = "http://sudarmuthu.com/wordpress/email-log/pro-addons#more-fields-addon">', __('More Info', 'email-log'), '</a>.';
            echo ' <a href = "http://sudarmuthu.com/out/buy-email-log-more-fields-addon">', __('Buy now', 'email-log'), '</a>';
            echo '</li>';

            echo '</ul>';
		}
	}

    /**
     * Return the list of column and title names
     *
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     */
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'sent_date' => __('Sent at', 'email-log'),
            'to'        => __('To', 'email-log'),
            'subject'   => __('Subject', 'email-log')
        );

        return apply_filters( EmailLog::HOOK_LOG_COLUMNS, $columns );
    }

    /**
     * Return the list of columns
     *
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     */
    function get_sortable_columns() {
        $sortable_columns = array(
            'sent_date'   => array('sent_date',TRUE),     //true means it's already sorted
            'to'          => array('to_email',FALSE),
            'subject'     => array('subject',FALSE)
        );
        return $sortable_columns;
    }

    /**
     * Return values for default columns
     */
    function column_default( $item, $column_name ){
        do_action( EmailLog::HOOK_LOG_DISPLAY_COLUMNS, $column_name, $item );
    }

    /**
     * Display sent date column
     */
    function column_sent_date($item) {

        //Build row actions
        $actions = array(
            'delete' => sprintf( '<a href="?page=%s&action=%s&%s=%s&%s=%s">%s</a>',
                                $_REQUEST['page'],
                                'delete',
                                $this->_args['singular'],
                                $item->id,
                                EmailLog::DELETE_LOG_NONCE_FIELD,
                                wp_create_nonce( EmailLog::DELETE_LOG_ACTION ),
                                __( 'Delete', 'email-log' )
                        ),
        );

        $email_date = mysql2date(
            sprintf( __( '%s @ %s', 'email-log' ), get_option( 'date_format', 'F j, Y' ), get_option( 'time_format', 'g:i A' ) ),
            $item->sent_date
        );

        return sprintf('%1$s <span style="color:silver">[<a href="#" class="email_content" id="email_content_%2$s">%3$s</a>] (id:%4$s)</span>%5$s',
            /*$1%s*/ $email_date,
            /*$2%s*/ $item->id,
            /*$3%s*/ __('View Content', 'email-log' ),
            /*$4%s*/ $item->id,
            /*$5%s*/ $this->row_actions($actions)
        );
    }

    /**
     * To field
     */
    function column_to( $item ) {
        return stripslashes( $item->to_email );
    }

    /**
     * Subject field
     */
    function column_subject( $item ) {
        return stripslashes( $item->subject );
    }

    /**
     * Markup for action column
     */
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],
            /*$2%s*/ $item->id
        );
    }

    /**
     * Specify the list of bulk actions
     *
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     */
    function get_bulk_actions() {
        $actions = array(
            'delete'     => __( 'Delete', 'email-log' ),
            'delete-all' => __( 'Delete All Logs', 'email-log' )
        );
        return $actions;
    }

    /**
     * Handle bulk actions
     *
     * @see $this->prepare_items()
     */
    function process_bulk_action() {
        global $wpdb;
        global $EmailLog;

        if( 'delete' === $this->current_action() ) {
            // delete a list of logs by id

            $nouce = $_REQUEST[EmailLog::DELETE_LOG_NONCE_FIELD ];
            if ( wp_verify_nonce( $nouce, EmailLog::DELETE_LOG_ACTION ) ) {

                $ids =  $_GET[$this->_args['singular']];

                if ( is_array( $ids ) ) {
                    $selected_ids = implode( ',', $ids );
                } else {
                    $selected_ids = $ids;
                }

                // Can't use wpdb->prepare for the below query. If used it results in this bug
                // https://github.com/sudar/email-log/issues/13

                $selected_ids = esc_sql( $selected_ids );

                $table_name = $wpdb->prefix . EmailLog::TABLE_NAME;
                $EmailLog->logs_deleted = $wpdb->query( "DELETE FROM $table_name where id IN ( $selected_ids )" );
            } else {
                wp_die( 'Cheating, Huh? ');
            }
        } else if( 'delete-all' === $this->current_action() ) {
            // delete all logs

            $nouce = $_REQUEST[EmailLog::DELETE_LOG_NONCE_FIELD ];
            if ( wp_verify_nonce( $nouce, EmailLog::DELETE_LOG_ACTION ) ) {
                $table_name = $wpdb->prefix . EmailLog::TABLE_NAME;
                $EmailLog->logs_deleted = $wpdb->query( "DELETE FROM $table_name" );
            } else {
                wp_die( 'Cheating, Huh? ');
            }
        }
    }

    /**
     * Prepare data for display.
     */
    function prepare_items() {
        global $wpdb;

        $table_name = $wpdb->prefix . EmailLog::TABLE_NAME;
        $this->_column_headers = $this->get_column_info();

        // Handle bulk actions
        $this->process_bulk_action();

        // get current page number
        $current_page = $this->get_pagenum();

        $query = "SELECT * FROM " . $table_name;

        if ( isset( $_GET['s'] ) ) {
            $search_term = trim( esc_sql( $_GET['s'] ) );
            $query .= " WHERE to_email LIKE '%$search_term%' OR subject LIKE '%$search_term%' ";
        }

        // Ordering parameters
	    $orderby = !empty( $_GET["orderby"] ) ? esc_sql( $_GET["orderby"] ) : 'sent_date';
	    $order   = !empty( $_GET["order"] ) ? esc_sql( $_GET["order"] ) : 'DESC';

        if(!empty($orderby) & !empty($order)) {
            $query .= ' ORDER BY ' . $orderby . ' ' . $order;
        }

        // Pagination parameters
        $total_items = $wpdb->query( $query ); //return the total number of affected rows

        //adjust the query to take pagination into account
        $per_page = EmailLog::get_per_page();
	    if( !empty( $current_page ) && !empty( $per_page ) ) {
		    $offset = ($current_page-1) * $per_page;
            $query .= ' LIMIT ' . (int)$offset . ',' . (int)$per_page;
	    }

        // Fetch the items
        $this->items = $wpdb->get_results( $query );

        // register pagination options & calculations.
        $this->set_pagination_args( array(
            'total_items' => $total_items,
            'per_page'    => $per_page,
            'total_pages' => ceil( $total_items/$per_page )
        ) );
    }

    /**
     * If no items are found
     */
    function no_items() {
        _e('Your email log is empty', 'email-log');
    }
}
?>
