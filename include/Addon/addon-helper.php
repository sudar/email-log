<?php
/**
 * Add-on helper functions.
 * These functions are not using namespace since they may be used from a PHP 5.2 file.
 *
 * @since 2.0
 */

/**
 * Load an Email Log add-on.
 *
 * @since 2.0
 *
 * @param string $addon_class Add-on class name.
 * @param string $plugin_file Plugin File.
 */
function load_email_log_addon( $addon_class, $plugin_file ) {
	$email_log = email_log();

	$plugin_dir = plugin_dir_path( $plugin_file );
	$email_log->loader->add_namespace( 'EmailLog', $plugin_dir . 'include' );

	$addon = new $addon_class( $plugin_file );

	add_action( 'el_loaded', array( $addon, 'load' ) );
}
