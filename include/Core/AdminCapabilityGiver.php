<?php namespace EmailLog\Core;

use EmailLog\Core\UI\Page\LogListPage;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Gives capability to admin.
 * By default admins should be able to manage email logs.
 *
 * @since 2.1.0
 */
class AdminCapabilityGiver implements Loadie {

	public function load() {
		add_filter( 'user_has_cap', array( $this, 'add_cap_to_admin_cap_list' ), 10, 4 );
	}

	/**
	 * Add `manage_email_logs` capability to admin's list of capabilities during `user_has_cap` check.
	 *
	 * In new installs this capability will be added to admins on plugin install.
	 * But in old installs the admins will not have this capability and that's why we need to add it using the filter.
	 *
	 * @param array    $allcaps An array of all the user's capabilities.
	 * @param array    $caps    Actual capabilities for meta capability.
	 * @param array    $args    Optional parameters passed to has_cap(), typically object ID.
	 * @param \WP_User $user    The user object.
	 *
	 * @return array Modified list of user's capabilities.
	 */
	public function add_cap_to_admin_cap_list( $allcaps, $caps, $args, $user ) {
		if ( ! in_array( 'administrator', $user->roles ) ) {
			return $allcaps;
		}

		if ( array_key_exists( LogListPage::CAPABILITY, $allcaps ) ) {
			return $allcaps;
		}

		$allcaps[ LogListPage::CAPABILITY ] = true;

		return $allcaps;
	}

	/**
	 * Add Manage Email Logs capability to administrator role.
	 * This will be called on install.
	 */
	public function add_cap_to_admin() {
		$admin = get_role( 'administrator' );

		if ( is_null( $admin ) ) {
			return;
		}

		$admin->add_cap( LogListPage::CAPABILITY );
	}
}
