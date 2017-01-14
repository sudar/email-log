<?php namespace EmailLog\Core\UI\Addon;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Render the License fields for an add-on.
 *
 * @since 2.0.0
 */
class AddonLicenseRenderer {

	private $addon_name;
	private $addon_version;

	/**
	 * Set Add-on data.
	 *
	 * @param string $addon_name    Add-on Name.
	 * @param string $addon_version Add-on Version.
	 */
	public function set_addon_data( $addon_name, $addon_version ) {
		$this->addon_name = $addon_name;
		$this->addon_version = $addon_version;
	}

	/**
	 * Setup hooks.
	 * This method is called on `wp-loaded` hook.
	 */
	public function load() {

	}
}
