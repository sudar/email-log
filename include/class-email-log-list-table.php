<?php
/**
 * Table to display Email Logs
 *
 * Based on Custom List Table Example by Matt Van Andel
 *
 * @package Email Log
 * @author Sudar
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
            'ajax'      => false        //does this table support ajax?
        ) );
    }


	/**
	 * Add extra markup in the toolbars before or after the list
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
    
    /** ************************************************************************
     * REQUIRED! This method dictates the table's columns and titles. This should
     * return an array where the key is the column slug (and class) and the value 
     * is the column's title text. If you need a checkbox for bulk actions, refer
     * to the $columns array below.
     * 
     * The 'cb' column is treated differently than the rest. If including a checkbox
     * column in your table you must create a column_cb() method. If you don't need
     * bulk actions or checkboxes, simply leave the 'cb' entry out of your array.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @return array An associative array containing column information: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_columns(){
        $columns = array(
            'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
            'sent_date' => __('Sent at', 'email-log'),
            'to'        => __('To', 'email-log'),
            'subject'   => __('Subject', 'email-log')
        );

        return apply_filters( EmailLog::HOOK_LOG_COLUMNS, $columns );
    }
    
    /** ************************************************************************
     * Optional. If you want one or more columns to be sortable (ASC/DESC toggle), 
     * you will need to register it here. This should return an array where the 
     * key is the column that needs to be sortable, and the value is db column to 
     * sort by. Often, the key and value will be the same, but this is not always
     * the case (as the value is a column name from the database, not the list table).
     * 
     * This method merely defines which columns should be sortable and makes them
     * clickable - it does not handle the actual sorting. You still need to detect
     * the ORDERBY and ORDER querystring variables within prepare_items() and sort
     * your data accordingly (usually by modifying your query).
     * 
     * @return array An associative array containing all the columns that should be sortable: 'slugs'=>array('data_values',bool)
     **************************************************************************/
    function get_sortable_columns() {
        $sortable_columns = array(
            'sent_date'   => array('sent_date',TRUE),     //true means it's already sorted
            'to'          => array('to_email',FALSE),
            'subject'     => array('subject',FALSE)
        );
        return $sortable_columns;
    }
    
    /** ************************************************************************
     * Recommended. This method is called when the parent class can't find a method
     * specifically build for a given column. Generally, it's recommended to include
     * one method for each column you want to render, keeping your package class
     * neat and organized. For example, if the class needs to process a column
     * named 'title', it would first see if a method named $this->column_title() 
     * exists - if it does, that method will be used. If it doesn't, this one will
     * be used. Generally, you should try to use custom column methods as much as 
     * possible. 
     * 
     * Since we have defined a column_title() method later on, this method doesn't
     * need to concern itself with any column with a name of 'title'. Instead, it
     * needs to handle everything else.
     * 
     * For more detailed insight into how columns are handled, take a look at 
     * WP_List_Table::single_row_columns()
     * 
     * @param array $item A singular item (one full row's worth of data)
     * @param array $column_name The name/slug of the column to be processed
     * @return string Text or HTML to be placed inside the column <td>
     **************************************************************************/
    function column_default( $item, $column_name ){
        do_action( EmailLog::HOOK_LOG_DISPLAY_COLUMNS, $column_name, $item );
    }
        
    /** ************************************************************************
     * Recommended. This is a custom column method and is responsible for what
     * is rendered in any column with a name/slug of 'title'. Every time the class
     * needs to render a column, it first looks for a method named 
     * column_{$column_title} - if it exists, that method is run. If it doesn't
     * exist, column_default() is called instead.
     * 
     * This example also illustrates how to implement rollover actions. Actions
     * should be an associative array formatted as 'slug'=>'link html' - and you
     * will need to generate the URLs yourself. You could even ensure the links
     * 
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td>
     **************************************************************************/
    function column_sent_date($item){
        
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

        $email_date = mysql2date(sprintf(__('%s @ %s', 'email-log'), get_option('date_format'), get_option('time_format')), $item->sent_date);

        return sprintf('%1$s <span style="color:silver">(id:%2$s)</span>%3$s',
            /*$1%s*/ $email_date,
            /*$2%s*/ $item->id,
            /*$3%s*/ $this->row_actions($actions)
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

    /** ************************************************************************
     * REQUIRED if displaying checkboxes or using bulk actions! The 'cb' column
     * is given special treatment when columns are processed. It ALWAYS needs to
     * have it's own method.
     * 
     * @see WP_List_Table::::single_row_columns()
     * @param array $item A singular item (one full row's worth of data)
     * @return string Text to be placed inside the column <td> (movie title only)
     **************************************************************************/
    function column_cb($item){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            /*$1%s*/ $this->_args['singular'],
            /*$2%s*/ $item->id
        );
    }
    
    
    /** ************************************************************************
     * Optional. If you need to include bulk actions in your list table, this is
     * the place to define them. Bulk actions are an associative array in the format
     * 'slug'=>'Visible Title'
     * 
     * If this method returns an empty value, no bulk action will be rendered. If
     * you specify any bulk actions, the bulk actions box will be rendered with
     * the table automatically on display().
     * 
     * Also note that list tables are not automatically wrapped in <form> elements,
     * so you will need to create those manually in order for bulk actions to function.
     * 
     * @return array An associative array containing all the bulk actions: 'slugs'=>'Visible Titles'
     **************************************************************************/
    function get_bulk_actions() {
        $actions = array(
            'delete'    => __('Delete', 'email-log')
        );
        return $actions;
    }
    
    /**
     * Handle bulk actions
     * 
     * @see $this->prepare_items()
     */
    function process_bulk_action() {
        global $EmailLog;
        global $wpdb;

        //Detect when a bulk action is being triggered...
        if( 'delete' === $this->current_action() ) {
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

                $EmailLog->logs_deleted = $wpdb->query( "DELETE FROM $EmailLog->table_name where id IN ( $selected_ids )" );
            } else {
                wp_die( 'Cheating, Huh? ');
            }
        }
    }
    
    /**
     * Prepare data for display.
     */
    function prepare_items( $per_page ) {
        global $wpdb;
        global $EmailLog;

        $this->_column_headers = $this->get_column_info();
        
        // Handle bulk actions
        $this->process_bulk_action();
        
        // get current page number
        $current_page = $this->get_pagenum();
        
        $query = "SELECT * FROM " . $EmailLog->table_name;

        if ( isset( $_GET['s'] ) ) {
            $search_term = trim( esc_sql( $_GET['s'] ) );
            $query .= " WHERE to_email LIKE '%$search_term%' OR subject LIKE '%$search_term%' ";
        }

        // Ordering parameters
	    $orderby = !empty( $_GET["orderby"] ) ? esc_sql( $_GET["orderby"] ) : 'sent_date';
	    $order = !empty( $_GET["order"] ) ? esc_sql( $_GET["order"] ) : 'DESC';

        if(!empty($orderby) & !empty($order)) {
            $query .= ' ORDER BY ' . $orderby . ' ' . $order; 
        }

        // Pagination parameters
        $total_items = $wpdb->query( $query ); //return the total number of affected rows

        //adjust the query to take pagination into account
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
