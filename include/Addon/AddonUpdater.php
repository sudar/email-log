<?php namespace EmailLog\Addon;

use EmailLog\Addon\API\EDDUpdater;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Add-on Updater.
 * Auto updates add-on based on EDD SL API.
 *
 * @since 2.0.0
 */
class AddonUpdater {

	private $addon_file;
	private $addon_name;
	private $addon_version;
	private $addon_author;

	/**
	 * Create a new instance of AddonUpdater.
	 *
	 * @param string $addon_file Add-on main file.
	 */
	public function __construct( $addon_file ) {
		$this->addon_file = $addon_file;
	}

	/**
	 * Set Add-on data.
	 *
	 * @param string $addon_name    Add-on Name.
	 * @param string $addon_version Add-on Version.
	 * @param string $addon_author  Add-on Author.
	 */
	public function set_addon_data( $addon_name, $addon_version, $addon_author ) {
		$this->addon_name    = $addon_name;
		$this->addon_version = $addon_version;
		$this->addon_author  = $addon_author;
	}

	/**
	 * Set up hooks and load the license handler.
	 * This method is called on `wp-loaded` hook.
	 */
	public function load() {
		add_action( 'admin_init', array( $this, 'setup_updater' ) );
	}

	/**
	 * Setup up Add-on auto-updater using EDD library.
	 */
	public function setup_updater() {
		$email_log = email_log();
		$licenser  = $email_log->get_licenser();

		if ( is_null( $licenser ) ) {
			return;
		}

		$license_key = $licenser->get_addon_license_key( $this->addon_name );

		$updater = new EDDUpdater( $email_log->get_store_url(), $this->addon_file, array(
				'version'   => $this->addon_version,
				'license'   => $license_key,
				'item_name' => $this->addon_name,
				'author'    => $this->addon_author,
			)
		);

		$licenser->add_updater( $updater );
	}
}
