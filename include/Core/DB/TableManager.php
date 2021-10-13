<?php namespace EmailLog\Core\DB;

/**
 * Handle installation and db table creation.
 */

use EmailLog\Core\Loadie;
use EmailLog\Util;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Helper class to create table.
 *
 * @since 2.0.0
 */
class TableManager implements Loadie {

	/* Database table name */
	const LOG_TABLE_NAME = 'email_log';

	/* Database option name */
	const DB_OPTION_NAME = 'email-log-db';

	/* Database version */
	const DB_VERSION = '0.3';

	/**
	 * Setup hooks.
	 */
	public function load() {
		add_action( 'wpmu_new_blog', array( $this, 'create_table_for_new_blog' ) );

		add_filter( 'wpmu_drop_tables', array( $this, 'delete_table_from_deleted_blog' ) );

		// Do any DB upgrades.
		$this->update_table_if_needed();
	}

	/**
	 * On plugin activation, create table if needed.
	 *
	 * @param bool $network_wide True if the plugin was network activated.
	 */
	public function on_activate( $network_wide ) {
		if ( is_multisite() && $network_wide ) {
			// Note: if there are more than 10,000 blogs or
			// if `wp_is_large_network` filter is set, then this may fail.
			$sites = get_sites();

			foreach ( $sites as $site ) {
				switch_to_blog( $site->blog_id );
				$this->create_table_if_needed();
				restore_current_blog();
			}
		} else {
			$this->create_table_if_needed();
		}
	}

	/**
	 * Create email log table when a new blog is created.
	 *
	 * @param int $blog_id Blog Id.
	 */
	public function create_table_for_new_blog( $blog_id ) {
		if ( is_plugin_active_for_network( 'email-log/email-log.php' ) ) {
			switch_to_blog( $blog_id );
			$this->create_table_if_needed();
			restore_current_blog();
		}
	}

	/**
	 * Add email log table to the list of tables deleted when a blog is deleted.
	 *
	 * @param array $tables List of tables to be deleted.
	 *
	 * @return string[] $tables Modified list of tables to be deleted.
	 */
	public function delete_table_from_deleted_blog( $tables ) {
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
	 * Delete log entries by ids.
	 *
	 * @param string $ids Comma separated list of log ids.
	 *
	 * @return false|int Number of log entries that got deleted. False on failure.
	 */
	public function delete_logs( $ids ) {
		global $wpdb;

		$table_name = $this->get_log_table_name();

		// Can't use wpdb->prepare for the below query. If used it results in this bug // https://github.com/sudar/email-log/issues/13.
		$ids = esc_sql( $ids );

		return $wpdb->query( "DELETE FROM {$table_name} where id IN ( {$ids} )" ); //@codingStandardsIgnoreLine
	}

	/**
	 * Delete all log entries.
	 *
	 * @return false|int Number of log entries that got deleted. False on failure.
	 */
	public function delete_all_logs() {
		global $wpdb;

		$table_name = $this->get_log_table_name();

		return $wpdb->query( "DELETE FROM {$table_name}" ); //@codingStandardsIgnoreLine
	}

	/**
	 * Deletes Email Logs older than the specified interval.
	 *
	 * @param int $interval_in_days No. of days beyond which logs are to be deleted.
	 *
	 * @return int $deleted_rows_count  Count of rows deleted.
	 */
	public function delete_logs_older_than( $interval_in_days ) {
		global $wpdb;
		$table_name = $this->get_log_table_name();

		$query              = $wpdb->prepare( "DELETE FROM {$table_name} WHERE sent_date < DATE_SUB( CURDATE(), INTERVAL %d DAY )", $interval_in_days );
		$deleted_rows_count = $wpdb->query( $query );

		return $deleted_rows_count;
	}

	/**
	 * Fetch log item by ID.
	 *
	 * @param array $ids             Optional. Array of IDs of the log items to be retrieved.
	 * @param array $additional_args {
	 *                               Optional. Array of additional args.
	 *
	 * @type string $date_column_format MySQL date column format. Refer
	 *
	 * @link  https://dev.mysql.com/doc/refman/5.5/en/date-and-time-functions.html#function_date-format
	 * }
	 *
	 * @return array Log item(s).
	 */
	public function fetch_log_items_by_id( $ids = array(), $additional_args = array() ) {
		global $wpdb;
		$table_name = $this->get_log_table_name();

		$query = "SELECT * FROM {$table_name}";

		// When `date_column_format` exists, should replace the `$query` var.
		$date_column_format_key = 'date_column_format';
		if ( array_key_exists( $date_column_format_key, $additional_args ) && ! empty( $additional_args[ $date_column_format_key ] ) ) {
			$query = "SELECT DATE_FORMAT(sent_date, \"{$additional_args[ $date_column_format_key ]}\") as sent_date_custom, el.* FROM {$table_name} as el";
		}

		if ( ! empty( $ids ) ) {
			$ids = array_map( 'absint', $ids );

			// Can't use wpdb->prepare for the below query. If used it results in this bug https://github.com/sudar/email-log/issues/13.
			$ids_list = esc_sql( implode( ',', $ids ) );

			$query .= " where id IN ( {$ids_list} )";
		}

		return $wpdb->get_results( $query, 'ARRAY_A' ); //@codingStandardsIgnoreLine
	}

	/**
	 * Fetch log items.
	 *
	 * @since 2.3.0 Implemented Advanced Search. Search queries could look like the following.
	 *              Example:
	 *              id: 2
	 *              to: sudar@sudarmuthu.com
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

		if ( isset( $request['s'] ) && is_string( $request['s'] ) && $request['s'] !== '' ) {
			$search_term = trim( esc_sql( $request['s'] ) );

			if ( Util\is_advanced_search_term( $search_term ) ) {
				$predicates = Util\get_advanced_search_term_predicates( $search_term );

				foreach ( $predicates as $column => $email ) {
					switch ( $column ) {
						case 'id':
							$query_cond .= empty( $query_cond ) ? ' WHERE ' : ' AND ';
							$query_cond .= "id = '$email'";
							break;
						case 'to':
							$query_cond .= empty( $query_cond ) ? ' WHERE ' : ' AND ';
							$query_cond .= "to_email LIKE '%$email%'";
							break;
						case 'email':
							$query_cond .= empty( $query_cond ) ? ' WHERE ' : ' AND ';
							$query_cond .= ' ( '; /* Begin 1st */
							$query_cond .= " ( to_email LIKE '%$email%' OR subject LIKE '%$email%' ) "; /* Begin 2nd & End 2nd */
							$query_cond .= ' OR ';
							$query_cond .= ' ( '; /* Begin 3rd */
							$query_cond .= "headers <> ''";
							$query_cond .= ' AND ';
							$query_cond .= ' ( '; /* Begin 4th */
							$query_cond .= "headers REGEXP '[F|f]rom:.*$email' OR ";
							$query_cond .= "headers REGEXP '[CC|Cc|cc]:.*$email' OR ";
							$query_cond .= "headers REGEXP '[BCC|Bcc|bcc]:.*$email' OR ";
							$query_cond .= "headers REGEXP '[R|r]eply-[T|t]o:.*$email'";
							$query_cond .= ' ) '; /* End 4th */
							$query_cond .= ' ) '; /* End 3rd */
							$query_cond .= ' ) '; /* End 1st */
							break;
						case 'cc':
							$query_cond .= empty( $query_cond ) ? ' WHERE ' : ' AND ';
							$query_cond .= ' ( '; /* Begin 1st */
							$query_cond .= "headers <> ''";
							$query_cond .= ' AND ';
							$query_cond .= ' ( '; /* Begin 2nd */
							$query_cond .= "headers REGEXP '[CC|Cc|cc]:.*$email' ";
							$query_cond .= ' ) '; /* End 2nd */
							$query_cond .= ' ) '; /* End 1st */
							break;
						case 'bcc':
							$query_cond .= empty( $query_cond ) ? ' WHERE ' : ' AND ';
							$query_cond .= ' ( '; /* Begin 1st */
							$query_cond .= "headers <> ''";
							$query_cond .= ' AND ';
							$query_cond .= ' ( '; /* Begin 2nd */
							$query_cond .= "headers REGEXP '[BCC|Bcc|bcc]:.*$email' ";
							$query_cond .= ' ) '; /* End 2nd */
							$query_cond .= ' ) '; /* End 1st */
							break;
						case 'reply-to':
							$query_cond .= empty( $query_cond ) ? ' WHERE ' : ' AND ';
							$query_cond .= ' ( '; /* Begin 1st */
							$query_cond .= "headers <> ''";
							$query_cond .= ' AND ';
							$query_cond .= ' ( '; /* Begin 2nd */
							$query_cond .= "headers REGEXP '[R|r]eply-to:.*$email' ";
							$query_cond .= ' ) '; /* End 2nd */
							$query_cond .= ' ) '; /* End 1st */
							break;
					}
				}
			} else {
				$query_cond .= " WHERE ( to_email LIKE '%$search_term%' OR subject LIKE '%$search_term%' ) ";
			}
		}

		if ( isset( $request['d'] ) && $request['d'] !== '' ) {
			$search_date = trim( esc_sql( $request['d'] ) );
			if ( '' === $query_cond ) {
				$query_cond .= " WHERE sent_date BETWEEN '$search_date 00:00:00' AND '$search_date 23:59:59' ";
			} else {
				$query_cond .= " AND sent_date BETWEEN '$search_date 00:00:00' AND '$search_date 23:59:59' ";
			}
		}

		// Ordering parameters.
		$order_by = 'sent_date';
		$order    = 'DESC';

		$allowed_order_by = [
			'sent_date',
			'to_email',
			'subject',
		];

		$sanitized_order_by = ( ! empty( $request['orderby'] ) ) ? sanitize_text_field( $request['orderby'] ) : '';
		if ( ! empty( $sanitized_order_by ) && in_array( $sanitized_order_by, $allowed_order_by, true ) ) {
			$order_by = $sanitized_order_by;
		}

		if ( ! empty( $request['order'] ) && 'asc' === strtolower( sanitize_text_field( $request['order'] ) ) ) {
			$order = 'ASC';
		}

		if ( ! empty( $order_by ) & ! empty( $order ) ) {
			$query_cond .= ' ORDER BY ' . $order_by . ' ' . $order;
		}

		// Find total number of items.
		$count_query = $count_query . $query_cond;
		$total_items = $wpdb->get_var( $count_query );

		// Adjust the query to take pagination into account.
		if ( ! empty( $current_page_no ) && ! empty( $per_page ) ) {
			$offset     = ( $current_page_no - 1 ) * $per_page;
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
	 * @global object $wpdb
	 */
	public function create_table_if_needed() {
		global $wpdb;

		$table_name = $this->get_log_table_name();

		if ( $wpdb->get_var( "show tables like '{$table_name}'" ) != $table_name ) {

			$sql = $this->get_create_table_query();

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			add_option( self::DB_OPTION_NAME, self::DB_VERSION );
		}
	}

	/**
	 * Get the total number of email logs.
	 *
	 * @return int Total email log count
	 */
	public function get_logs_count() {
		global $wpdb;

		$query = 'SELECT count(*) FROM ' . $this->get_log_table_name();

		return $wpdb->get_var( $query );
	}

	/**
	 * Fetches the log id by item data.
	 *
	 * Use this method to get the log item id when the error instance only returns the log item data.
	 *
	 * @param array        $data Array of Email information {
	 * @type  array|string to
	 * @type  string       subject
	 * @type  string       message
	 * @type  array|string headers
	 * @type  array|string attachments
	 *                          }
	 *
	 * @return int Log item id.
	 */
	public function fetch_log_id_by_data( $data ) {
		if ( empty( $data ) || ! is_array( $data ) ) {
			return 0;
		}

		global $wpdb;
		$table_name = $this->get_log_table_name();

		$query      = "SELECT ID FROM {$table_name}";
		$query_cond = '';
		$where      = array();

		// Execute the following `if` conditions only when $data is array.
		if ( array_key_exists( 'to', $data ) ) {
			// Since the value is stored as CSV in DB, convert the values from error data to CSV to compare.
			$to_email = Util\stringify( $data['to'] );

			$to_email = trim( esc_sql( $to_email ) );
			$where[]  = "to_email = '$to_email'";
		}

		if ( array_key_exists( 'subject', $data ) ) {
			$subject = trim( esc_sql( $data['subject'] ) );
			$where[] = "subject = '$subject'";
		}

		if ( array_key_exists( 'attachments', $data ) ) {
			if ( is_array( $data['attachments'] ) ) {
				$attachments = count( $data['attachments'] ) > 0 ? 'true' : 'false';
			} else {
				$attachments = empty( $data['attachments'] ) ? 'false' : 'true';
			}
			$attachments = trim( esc_sql( $attachments ) );
			$where[]     = "attachments = '$attachments'";
		}

		foreach ( $where as $index => $value ) {
			$query_cond .= 0 === $index ? ' WHERE ' : ' AND ';
			$query_cond .= $value;
		}

		// Get only the latest logged item when multiple rows match.
		$query_cond .= ' ORDER BY id DESC LIMIT 1';

		$query = $query . $query_cond;

		return absint( $wpdb->get_var( $query ) );
	}

	/**
	 * Sets email sent status and error message for the given log item when email fails.
	 *
	 * @param int    $log_item_id ID of the log item whose email sent status should be set to failed.
	 * @param string $message     Error message.
	 *
	 * @since 2.4.0 Include error message during update.
	 * @since 2.3.0
	 *
	 * @global \wpdb $wpdb
	 *
	 * @see  TableManager::get_log_table_name()
	 */
	public function mark_log_as_failed( $log_item_id, $message ) {
		global $wpdb;
		$table_name = $this->get_log_table_name();

		$wpdb->update(
			$table_name,
			array(
				'result'        => '0',
				'error_message' => $message,
			),
			array( 'ID' => $log_item_id ),
			array(
				'%d', // `result` format.
				'%s', // `error_message` format.
			),
			array(
				'%d', // `ID` format.
			)
		);
	}

	/**
	 * Updates the DB schema.
	 *
	 * Adds new columns to the Database as of v0.2.
	 *
	 * @since 2.3.0
	 */
	private function update_table_if_needed() {
		if ( get_option( self::DB_OPTION_NAME, false ) === self::DB_VERSION ) {
			return;
		}

		$sql = $this->get_create_table_query();

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );

		update_option( self::DB_OPTION_NAME, self::DB_VERSION );
	}

	/**
	 * Gets the Create Table query.
	 *
	 * @since 2.4.0 Added error_message column.
	 * @since 2.3.0
	 *
	 * @return string
	 */
	private function get_create_table_query() {
		global $wpdb;
		$table_name      = $this->get_log_table_name();
		$charset_collate = $wpdb->get_charset_collate();

		$sql = 'CREATE TABLE ' . $table_name . ' (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				to_email VARCHAR(500) NOT NULL,
				subject VARCHAR(500) NOT NULL,
				message TEXT NOT NULL,
				headers TEXT NOT NULL,
				attachments TEXT NOT NULL,
				sent_date timestamp NOT NULL,
				attachment_name VARCHAR(1000),
				ip_address VARCHAR(15),
				result TINYINT(1),
				error_message VARCHAR(1000),
				PRIMARY KEY  (id)
			) ' . $charset_collate . ';';

		return $sql;
	}

	/**
	 * Callback for the Array filter.
	 *
	 * @since 2.3.0
	 *
	 * @param string $column A column from the array Columns.
	 *
	 * @return bool
	 */
	private function validate_columns( $column ) {
		return in_array( $column, array( 'to' ), true );
	}

	/**
	 * Query log items by column.
	 *
	 * @since 2.3.0
	 *
	 * @param array $columns Key value pair based on which items should be retrieved.
	 *
	 * @uses \EmailLog\Core\DB\TableManager::validate_columns()
	 *
	 * @return array|object|null
	 */
	public function query_log_items_by_column( $columns ) {
		if ( ! is_array( $columns ) ) {
			return;
		}

		// Since we support PHP v5.2.4, we cannot use ARRAY_FILTER_USE_KEY
		// TODO: PHP v5.5: Once WordPress updates minimum PHP version to v5.5, start using ARRAY_FILTER_USE_KEY.
		$columns_keys = array_keys( $columns );
		if ( ! array_filter( $columns_keys, array( $this, 'validate_columns' ) ) ) {
			return;
		}

		global $wpdb;

		$table_name = $this->get_log_table_name();
		$query      = "SELECT id, sent_date, to_email, subject FROM {$table_name}";
		$query_cond = '';
		$where      = array();

		// Execute the following `if` conditions only when $data is array.
		if ( array_key_exists( 'to', $columns ) ) {
			// Since the value is stored as CSV in DB, convert the values from error data to CSV to compare.
			$to_email = Util\stringify( $columns['to'] );

			$to_email = trim( esc_sql( $to_email ) );
			$where[]  = "to_email = '$to_email'";

			foreach ( $where as $index => $value ) {
				$query_cond .= 0 === $index ? ' WHERE ' : ' AND ';
				$query_cond .= $value;
			}

			// Get only the latest logged item when multiple rows match.
			$query_cond .= ' ORDER BY id DESC';

			$query = $query . $query_cond;

			return $wpdb->get_results( $query );
		}
	}
}
