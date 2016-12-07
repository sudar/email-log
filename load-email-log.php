<?php
/**
 * Load Email Log plugin.
 *
 * We need this load code in a separate file since it requires namespace
 * and using namespace in PHP 5.2 will generate a fatal error.
 *
 * @since 2.0
 */

/**
 * Load Email Log plugin.
 *
 * @since 2.0
 *
 * @param $plugin_file
 */
function load_email_log( $plugin_file ) {
	global $email_log;

	$plugin_dir = plugin_dir_path( $plugin_file );

	// setup autoloader.
	require_once 'include/EmailLogAutoloader.php';

	$loader = new \EmailLog\EmailLogAutoloader();
	$loader->add_namespace( 'EmailLog', $plugin_dir . 'include' );

	if ( file_exists( $plugin_dir . 'tests/' ) ) {
		// if tests are present, then add them.
		$loader->add_namespace( 'EmailLog', $plugin_dir . 'tests/wp-tests' );
	}

	$loader->add_file( $plugin_dir . 'include/Util/helper.php' );

	$loader->register();

	$email_log                = new \EmailLog\Core\EmailLog( $plugin_file );
	$email_log->table_manager = new \EmailLog\Core\DB\TableManager();
	$email_log->logger        = new \EmailLog\Core\EmailLogger();
	$email_log->ui_manager    = new \EmailLog\Core\UI\UIManager( $plugin_file );

	// `register_activation_hook` can't be called from inside any hook.
	register_activation_hook( $plugin_file, array( $email_log->table_manager, 'on_activate' ) );

	// Load the plugin
	add_action( 'wp_loaded', array( $email_log, 'load' ) );
}

/**
 * Return the global instance of Email Log plugin.
 * Eventually the EmailLog class might become singleton.
 *
 * @since 2.0
 *
 * @global \EmailLog\Core\EmailLog $email_log
 * @return \EmailLog\Core\EmailLog
 */
function email_log() {
	global $email_log;

	return $email_log;
}
