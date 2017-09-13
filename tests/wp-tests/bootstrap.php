<?php

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

$_uninstall_test_lib_exists = false;
if ( file_exists( dirname( __FILE__ ) . '/../../vendor/jdgrimes/wp-plugin-uninstall-tester/' ) ) {
	$_uninstall_test_lib_exists = true;
}

if ( $_uninstall_test_lib_exists ) {
	// Include the uninstall test tools functions.
	include_once dirname( __FILE__ ) . '/../../vendor/jdgrimes/wp-plugin-uninstall-tester/includes/functions.php';
}

function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/../email-log.php';
}

// Check if the tests are running. Only load the plugin if they aren't.
if ( ! running_wp_plugin_uninstall_tests() ) {
	tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );
}

require $_tests_dir . '/includes/bootstrap.php';

if ( $_uninstall_test_lib_exists ) {
	include_once dirname( __FILE__ ) . '/../../vendor/jdgrimes/wp-plugin-uninstall-tester/bootstrap.php';
}
