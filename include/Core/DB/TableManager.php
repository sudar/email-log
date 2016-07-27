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
}
