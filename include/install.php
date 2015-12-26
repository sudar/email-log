<?php
/**
 * Handle installation and db table creation
 *
 * @package     Email Log
 * @subpackage  Install
 * @author      Sudar
 * @since       1.7
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Helper class to create and maintain tables
 *
 * @author Sudar
 */
class Email_Log_Init {

	/**
	 * Perform activation based on multisite or not
	 *
	 * @since 1.7
	 * @static
	 * @access public
	 *
	 * @global object $wpdb
	 */
	public static function on_activate( $network_wide ) {
		global $wpdb;

		if ( is_multisite() && $network_wide ) {
			// store the current blog id
			$current_blog = $wpdb->blogid;

			// Get all blogs in the network and activate plugin on each one
			$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );
			foreach ( $blog_ids as $blog_id ) {
				switch_to_blog( $blog_id );
				self::create_emaillog_table();
				restore_current_blog();
			}
		} else {
			self::create_emaillog_table();
		}
	}

	/**
	 * Create email log table when a new blog is created
	 *
	 * @since 1.7
	 * @static
	 * @access public
	 */
	public static function on_create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
		if ( is_plugin_active_for_network( 'email-log/email-log.php' ) ) {
			switch_to_blog( $blog_id );
			self::create_emaillog_table();
			restore_current_blog();
		}
	}

	/**
	 * Delete email log table when a blog is deleted
	 *
	 * @since  1.7
	 * @static
	 * @access public
	 *
	 * @global object $wpdb
	 * @param  array  $tables List of tables to be deleted
	 * @return array  $tables Modified list of tables to be deleted
	 */
	public static function on_delete_blog( $tables ) {
		global $wpdb;
		$tables[] = $wpdb->prefix . EmailLog::TABLE_NAME;
		return $tables;
	}

	/**
	 * Create email log table
	 *
	 * @since  1.7
	 * @static
	 * @access private
	 *
	 * @global object $wpdb
	 */
	private static function create_emaillog_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . EmailLog::TABLE_NAME;
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

			add_option( EmailLog::DB_OPTION_NAME, EmailLog::DB_VERSION );
		}
	}
}

// When the Plugin installed
register_activation_hook( EMAIL_LOG_PLUGIN_FILE, array( 'Email_Log_Init', 'on_activate' ) );

// when a new blog is created in multisite
add_action( 'wpmu_new_blog', array( 'Email_Log_Init', 'on_create_blog' ), 10, 6 );

// when a blog is deleted in multisite
add_filter( 'wpmu_drop_tables', array( 'Email_Log_Init', 'on_delete_blog' ) );
?>
