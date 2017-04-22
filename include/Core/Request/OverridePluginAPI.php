<?php namespace EmailLog\Core\Request;

use EmailLog\Addon\AddonList;
use EmailLog\Addon\API\EDDUpdater;
use EmailLog\Core\Loadie;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Override WordPress Plugin API.
 * This is already done by EDD_SL_Plugin_Updater for Active add-on
 * and this class does it for all in active or yet to be installed add-ons.
 *
 * @since 2.0.0
 */
class OverridePluginAPI implements Loadie {

	/**
	 * Setup actions.
	 *
	 * @inheritdoc
	 */
	public function load() {
		add_action( 'admin_init', array( $this, 'setup_updaters_for_inactive_addons' ) );
	}

	/**
	 * Setup updaters for all in-active addons.
	 */
	public function setup_updaters_for_inactive_addons() {
		$email_log = email_log();
		$inactive_addons = $email_log->get_licenser()->get_addon_list()->get_inactive_addons();

		foreach ( $inactive_addons as $inactive_addon ) {
			$license_key = $email_log->get_licenser()->get_addon_license_key( $inactive_addon->name );

			$updater = new EDDUpdater( $email_log->get_store_url(), $inactive_addon->file, array(
					'version'   => $inactive_addon->get_version(),
					'license'   => $license_key,
					'item_name' => $inactive_addon->name,
					'author'    => $inactive_addon->author,
				)
			);

			$email_log->get_licenser()->add_updater( $updater );
		}
	}
}
