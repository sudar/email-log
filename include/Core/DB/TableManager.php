<?php namespace EmailLog\Core\DB;
/**
 * Handle installation and db table creation
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Helper class to create table.
 *
 * @since 2.0
 */
class TableManager {

	/* Database table name */
	const LOG_TABLE_NAME = 'email_log';

	/* Database option name */
	const DB_OPTION_NAME = 'email-log-db';

	/* Database version */
	const DB_VERSION = '0.1';

	/**
	 * Setup hooks.
	 */
	public function load() {
		// when a new blog is created in multisite
		add_action( 'wpmu_new_blog', array( $this, 'on_create_blog' ), 10, 6 );

		// when a blog is deleted in multisite
		add_filter( 'wpmu_drop_tables', array( $this, 'on_delete_blog' ) );
	}

	/**
	 * On plugin activation, create table if needed.
	 */
	public function on_activate( $network_wide ) {
		if ( is_multisite() && $network_wide ) {
			// Note: if there are more than 10,000 blogs or
			// if `wp_is_large_network` filter is set, then this may fail.
			$sites = wp_get_sites();

			foreach ( $sites as $site ) {
				switch_to_blog( $site['blog_id'] );
				$this->create_table();
				restore_current_blog();
			}
		} else {
			$this->create_table();
		}
	}

	/**
	 * Create email log table when a new blog is created.
	 */
	public function on_create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
		if ( is_plugin_active_for_network( 'email-log/email-log.php' ) ) {
			switch_to_blog( $blog_id );
			$this->create_table();
			restore_current_blog();
		}
	}

	/**
	 * Add email log table to the list of tables deleted when a blog is deleted.
	 *
	 * @param  array $tables List of tables to be deleted.
	 *
	 * @return string[]  $tables Modified list of tables to be deleted.
	 */
	public function on_delete_blog( $tables ) {
		$tables[] = $this->get_log_table_name();

		return $tables;
	}

	/**
	 * Get email log table name.
	 *
	 * @return string Email Log Table name.
	 */
	public function get_log_table_name() {
		global $wpdb;

		return $wpdb->prefix . self::LOG_TABLE_NAME;
	}

	/**
	 * Insert log data into DB.
	 *
	 * @param array $data Data to be inserted.
	 */
	public function insert_log( $data ) {
		global $wpdb;

		$table_name = $this->get_log_table_name();
		$wpdb->insert( $table_name, $data );
	}

	/**
	 * Delete log entires by ids.
	 *
	 * @param string $ids Comma seperated list of log ids.
	 *
	 * @return false|int Number of log entries that got deleted. False on failure.
	 */
	public function delete_logs_by_id( $ids ) {
		global $wpdb;

		// Can't use wpdb->prepare for the below query. If used it results in this bug
		// https://github.com/sudar/email-log/issues/13

		$ids        = esc_sql( $ids );
		$table_name = $this->get_log_table_name();

		return $wpdb->query( "DELETE FROM $table_name where id IN ( $ids )" ); //@codingStandardsIgnoreLine
	}

	/**
	 * Delete all log entries.
	 *
	 * @return false|int Number of log entries that got deleted. False on failure.
	 */
	public function delete_all_logs() {
		global $wpdb;

		$table_name = $this->get_log_table_name();

		return $wpdb->query( "DELETE FROM $table_name" ); //@codingStandardsIgnoreLine
	}

	/**
	 * Get message by log id.
	 *
	 * @param int $id Id of the log whose message is needed.
	 *
	 * @return null|string Message of the log with $id.
	 */
	public function get_log_message( $id ) {
		global $wpdb;

		$table_name = $this->get_log_table_name();
		$query      = $wpdb->prepare( 'SELECT message FROM ' . $table_name . ' WHERE id = %d', $id );

		return $wpdb->get_var( $query );
	}

	/**
	 * Fetch log items.
	 *
	 * @param array $request         Request object.
	 * @param int   $per_page        Entries per page.
	 * @param int   $current_page_no Current page no.
	 *
	 * @return array Log entries and total items count.
	 */
	public function fetch_log_items( $request, $per_page, $current_page_no ) {
		global $wpdb;
		$table_name = $this->get_log_table_name();

		$query       = 'SELECT * FROM ' . $table_name;
		$count_query = 'SELECT count(*) FROM ' . $table_name;
		$query_cond  = '';

		if ( isset( $request['s'] ) && $request['s'] !== '' ) {
			$search_term = trim( esc_sql( $request['s'] ) );
			$query_cond .= " WHERE ( to_email LIKE '%$search_term%' OR subject LIKE '%$search_term%' ) ";
		}

		if ( isset( $request['d'] ) && $request['d'] !== '' ) {
			$search_date =  trim( esc_sql( $request['d'] ) );
			if ( '' === $query_cond ) {
				$query_cond .= " WHERE sent_date BETWEEN '$search_date 00:00:00' AND '$search_date 23:59:59' ";
			} else {
				$query_cond .= " AND sent_date BETWEEN '$search_date 00:00:00' AND '$search_date 23:59:59' ";
			}
		}

		// Ordering parameters.
		$orderby = ! empty( $request['orderby'] ) ? esc_sql( $request['orderby'] ) : 'sent_date';
		$order   = ! empty( $request['order'] ) ? esc_sql( $request['order'] ) : 'DESC';

		if ( ! empty( $orderby ) & ! empty( $order ) ) {
			$query_cond .= ' ORDER BY ' . $orderby . ' ' . $order;
		}

		// Find total number of items.
		$count_query = $count_query . $query_cond;
		$total_items = $wpdb->get_var( $count_query );

		// Adjust the query to take pagination into account.
		if ( ! empty( $current_page_no ) && ! empty( $per_page ) ) {
			$offset = ( $current_page_no - 1 ) * $per_page;
			$query_cond .= ' LIMIT ' . (int) $offset . ',' . (int) $per_page;
		}

		// Fetch the items.
		$query = $query . $query_cond;
		$items = $wpdb->get_results( $query );

		return array( $items, $total_items );
	}

	/**
	 * Create email log table.
	 *
	 * @access private
	 *
	 * @global object $wpdb
	 */
	private function create_table() {
		global $wpdb;

		$table_name      = $this->get_log_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		if ( $wpdb->get_var( "show tables like '{$table_name}'" ) != $table_name ) {

			$sql = 'CREATE TABLE ' . $table_name . ' (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				to_email VARCHAR(100) NOT NULL,
				subject VARCHAR(250) NOT NULL,
				message TEXT NOT NULL,
				headers TEXT NOT NULL,
				attachments TEXT NOT NULL,
				sent_date timestamp NOT NULL,
				PRIMARY KEY  (id)
			) ' . $charset_collate . ' ;';

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );

			add_option( self::DB_OPTION_NAME, self::DB_VERSION );
		}
	}

	/**
	 * Fetch log item by ID.
	 *
	 * @param int $id ID of the log item to be retrieved.
	 * @return array  Log item.
	 */
	public function fetch_log_item_by_id( $id ) {
		global $wpdb;
		$table_name = $this->get_log_table_name();

		$query      = $wpdb->prepare( 'SELECT * FROM ' . $table_name . ' WHERE id = %d', $id );
		return $wpdb->get_results( $query );
	}
}
