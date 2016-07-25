<?php
/**
 * Uninstall page for Email Log Plugin to clean up db.
 *
 * This file is named uninstall.php since WordPress requires that name.
 */

// exit if WordPress is not uninstalling the plugin.
if ( ! defined( 'ABSPATH' ) && ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

if ( is_multisite() ) {
	// Note: if there are more than 10,000 blogs or
	// if `wp_is_large_network` filter is set, then this may fail.
	$sites = wp_get_sites();

	foreach ( $sites as $site ) {
		switch_to_blog( $site['blog_id'] );
		email_log_delete_table();
		restore_current_blog();
	}
} else {
	email_log_delete_table();
}

/**
 * Delete email log table and db option
 *
 * @since 1.7
 *
 * @global object $wpdb
 */
function email_log_delete_table() {
	global $wpdb;

	// This is hardcoded on purpose, since the entire plugin is not loaded during uninstall.
	$table_name = $wpdb->prefix . 'email_log';

	if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) == $table_name ) {
		// If table is present, drop it
		$wpdb->query( "DROP TABLE $table_name" );
	}

	// Delete the option
	delete_option( 'email-log-db' );
}
