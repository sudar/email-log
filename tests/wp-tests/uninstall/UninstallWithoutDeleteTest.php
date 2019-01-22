<?php namespace EmailLog;

if ( ! class_exists( '\\WP_Plugin_Uninstall_UnitTestCase' ) ) {
	return;
}

/**
 * Plugin uninstall test case.
 *
 * @group uninstall
 */
class UninstallWithoutDeleteTest extends \WP_Plugin_Uninstall_UnitTestCase {
	/**
	 * The full path to the main plugin file.
	 *
	 * @type string $plugin_file
	 */
	protected $plugin_file;

	/**
	 * Set up for the tests.
	 */
	public function setUp() {
		$this->plugin_file = 'email-log/email-log.php';

		parent::setUp();
	}

	/**
	 * Test installation and uninstallation without deleting table.
	 */
	public function test_uninstall_without_deleting_table() {
		global $wpdb;

		/*
		 * First test that the plugin installed itself properly.
		 */

		// Check that a database table was added.
		$this->assertTableExists( $wpdb->prefix . 'email_log' );

		// Check that an option was added to the database.
		$this->assertEquals( '0.2', get_option( 'email-log-db' ) );

		/*
		 * Now, test that it uninstalls itself properly.
		 * By default table should not be deleted.
		 */

		// You must call this to perform uninstallation.
		$this->uninstall();

		// Check that the table was deleted.
		$this->assertTableExists( $wpdb->prefix . 'email_log' );
	}
}
