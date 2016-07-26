<?php namespace EmailLog;

if ( ! class_exists( '\\WP_Plugin_Uninstall_UnitTestCase' ) ) {
	return;
}

/**
 * Plugin uninstall test case.
 *
 * @group uninstall
 */
class UninstallTest extends \WP_Plugin_Uninstall_UnitTestCase {

	//
	// Protected properties.
	//

	/**
	 * The full path to the main plugin file.
	 *
	 * @type string $plugin_file
	 */
	protected $plugin_file;

	//
	// Public methods.
	//

	/**
	 * Set up for the tests.
	 */
	public function setUp() {

		// You must set the path to your plugin here.
		// This should be the path relative to the plugin directory on the test site.
		// You will need to copy or symlink your plugin's folder there if it isn't
		// already.
		$this->plugin_file = 'email-log/email-log.php';

		// Don't forget to call the parent's setUp(), or the plugin won't get installed.
		parent::setUp();
	}

	/**
	 * Test installation and uninstallation.
	 */
	public function test_uninstall() {
		global $wpdb;

		/*
		 * First test that the plugin installed itself properly.
		 */

		// Check that a database table was added.
		$this->assertTableExists( $wpdb->prefix . 'email_log' );

		// Check that an option was added to the database.
		$this->assertEquals( '0.1', get_option( 'email-log-db' ) );

		/*
		 * Now, test that it uninstalls itself properly.
		 */

		// You must call this to perform uninstallation.
		$this->uninstall();

		// Check that the table was deleted.
		$this->assertTableNotExists( $wpdb->prefix . 'email_log' );

		// Check that all options with a prefix was deleted.
		$this->assertNoOptionsWithPrefix( 'email-log' );
	}
}
