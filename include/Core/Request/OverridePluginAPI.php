<?php namespace EmailLog\Core\Request;

use EmailLog\Addon\AddonList;
use EmailLog\Core\Loadie;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Override WordPress Plugin API and inject add-on urls.
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
		add_filter( 'plugins_api', array( $this, 'inject_addon_install_resource' ), 10, 3 );
	}

	/**
	 * Inject add-on install resource into WordPress plugin API.
	 *
	 * @param  object|bool $res    Plugin resource object or boolean false.
	 * @param  string      $action The API call being performed.
	 * @param  object      $args   Arguments for the API call being performed.
	 *
	 * @return \stdClass Processed resource.
	 */
	public function inject_addon_install_resource( $res, $action, $args ) {
		if ( 'plugin_information' !== $action || empty( $args->slug ) ) {
			return $res;
		}

		$addon_list = new AddonList();
		$addon = $addon_list->get_addon_by_slug( $args->slug );

		if ( ! $addon ) {
			return $res;
		}

		$res                = new stdClass();
		$res->name          = 'Email Log - ' . $addon->name;
		$res->version       = ''; // TODO: Implement Version.
		$res->download_link = $addon->get_download_url();
		$res->tested        = false; // TODO: Implement tested up to.

		return $res;
	}
}
