<?php namespace EmailLog;

if ( ! class_exists( '\\WP_Plugin_Uninstall_UnitTestCase' ) ) {
	return;
}

/**
 * Plugin uninstall test case.
 *
 * @group uninstall-delete
 */
class UninstallWithDeleteTest extends \WP_Plugin_Uninstall_UnitTestCase {
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
	 * Test installation and uninstallation with deleting table.
	 */
	public function test_uninstall_with_deleting_table() {
		global $wpdb;

		/*
		 * First test that the plugin installed itself properly.
		 */

		// Check that a database table was added.
		$this->assertTableExists( $wpdb->prefix . 'email_log' );

		// Check that an option was added to the database.
		$this->assertEquals( '0.3', get_option( 'email-log-db' ) );

		// add the option that will delete the table during uninstall.
		$value = array(
			'allowed_user_roles'  => array(),
			'remove_on_uninstall' => 'true',
		);
		update_option( 'email-log-core', $value );

		$this->uninstall();

		// Check that the table was deleted.
		$this->assertTableNotExists( $wpdb->prefix . 'email_log' );

		// Check that all options with a prefix was deleted.
		$this->assertNoOptionsWithPrefix( 'email-log' );

		// check the capability has been removed from all user roles.
		$roles = get_editable_roles();
		foreach ( $roles as $role_name => $role_obj ) {
			$role = get_role( $role_name );

			if ( ! is_null( $role ) ) {
				$this->assertFalse( $role->has_cap( 'manage_email_logs' ), 'Capability is not cleaned up' );
			}
		}

		// check whether the license options got deleted.
		$this->assertNoOptionsWithPrefix( 'el_bundle_license' );
		$this->assertNoOptionsWithPrefix( 'el_license_' );
	}
}
