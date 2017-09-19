<?php
/**
 * Uninstall page for Email Log Plugin to clean up all plugin data.
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
		email_log_delete_db_data();
		restore_current_blog();
	}
} else {
	email_log_delete_db_data();
}

/**
 * Delete all email log data from db.
 *
 * The data include email log table, options, capability and add-on license data.
 *
 * @since 1.7
 *
 * @global object $wpdb
 */
function email_log_delete_db_data() {
	global $wpdb;

	$remove_data_on_uninstall = false;

	$option = get_option( 'email-log-core' );
	if ( is_array( $option ) && array_key_exists( 'remove_on_uninstall', $option ) &&
	     'true' === strtolower( $option['remove_on_uninstall'] ) ) {

		$remove_data_on_uninstall = true;
	}

	// This is hardcoded on purpose, since the entire plugin is not loaded during uninstall.
	$table_name = $wpdb->prefix . 'email_log';

	if ( $remove_data_on_uninstall ) {
		if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) == $table_name ) {
			$wpdb->query( "DROP TABLE $table_name" );
		}

		delete_option( 'email-log-db' );
		delete_option( 'email-log-core' );

		$roles = get_editable_roles();
		foreach ( $roles as $role_name => $role_obj ) {
			$role = get_role( $role_name );

			if ( ! is_null( $role ) ) {
				$role->remove_cap( 'manage_email_logs' );
			}
		}

		delete_option( 'el_bundle_license' );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'el_license_%'" );
	}
}
