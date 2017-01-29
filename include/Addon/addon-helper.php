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
 * @since 2.0.0
 *
 * @param string $addon_class Add-on class name.
 * @param string $addon_file  Add-on File.
 * @param array $args         Injecting dependency by passing required instantiated classes.
 */
function load_email_log_addon( $addon_class, $addon_file, $args = array() ) {
	$email_log = email_log();

	$addon_dir = plugin_dir_path( $addon_file );
	$email_log->loader->add_namespace( 'EmailLog', $addon_dir . 'include' );

	$license_renderer = new EmailLog\Core\UI\Addon\AddonLicenseRenderer();
	$license_handler = new EmailLog\Addon\AddonLicenseHandler( $addon_file, $license_renderer );

	$addon = new $addon_class( $addon_file, $license_handler, $args );

	add_action( 'el_loaded', array( $addon, 'load' ) );
}
