<?php
/**
 * Uninstall Email Log plugin
 *
 * @package     Email Log
 * @subpackage  Uninstall
 * @author      Sudar
*/
// uninstall page for Email Log Plugin to clean up db.
if( !defined( 'ABSPATH' ) && !defined( 'WP_UNINSTALL_PLUGIN' ) )
    exit();

if ( is_multisite() ) {
    global $wpdb;

    $original_blog_id = get_current_blog_id();

    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs" );

    foreach ( $blog_ids as $blog_id ) {
        switch_to_blog( $blog_id );
        email_log_delete_table();
    }

    switch_to_blog( $original_blog_id );

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
    $table_name = $wpdb->prefix . "email_log"; // This is hardcoded on purpose

    if( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) == $table_name ) {
        // If table is present, drop it
        $wpdb->query( "DROP TABLE $table_name" );
    }

    // Delete the option
    delete_option('email-log-db');
}
?>
