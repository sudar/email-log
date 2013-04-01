<?php
// uninstall page for Email Log Plugin to clean up db.
// Code based on this article http://jacobsantos.com/2008/general/wordpress-27-plugin-uninstall-methods/

if( !defined( 'ABSPATH') && !defined('WP_UNINSTALL_PLUGIN') )
    exit();

global $wpdb;
$smel_table_name = $wpdb->prefix . "email_log"; // This is hardcoded on purpose

if($wpdb->get_var("show tables like '{$smel_table_name}'") == $smel_table_name) {
    // If table is present, drop it
    $sql = "DROP TABLE $smel_table_name";
    $wpdb->query($sql);
}

// Delete the option
delete_option('email-log-db');
?>
