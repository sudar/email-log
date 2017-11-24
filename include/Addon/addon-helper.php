<?php
/**
 * Add-on helper functions.
 * These functions are not using namespace since they may be used from a PHP 5.2 file.
 *
 * @since 2.0
 */
defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Load an Email Log add-on.
 *
 * @since 2.0.0
 *
 * @param string $addon_class Add-on class name.
 * @param string $addon_file  Add-on File.
 *
 * @return \EmailLog\Addon\EmailLogAddon Instance of the add-on.
 */
function load_email_log_addon( $addon_class, $addon_file ) {
	$email_log = email_log();

	$addon_dir = plugin_dir_path( $addon_file );
	$email_log->loader->add_namespace( 'EmailLog', $addon_dir . 'include' );

	$addon_updater = null;

	if ( \EmailLog\Util\is_admin_non_ajax_request() ) {
		$addon_updater = new \EmailLog\Addon\AddonUpdater( $addon_file );
	}

	$addon = new $addon_class( $addon_file, $addon_updater );

	add_action( 'el_loaded', array( $addon, 'load' ) );

	return $addon;
}
