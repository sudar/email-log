<?php namespace EmailLog\Addon;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Addon License Handler.
 *
 * @since 2.0.0
 */
class AddonLicenseHandler {

	const STORE_URL = 'https://wpemaillog.com';

	private $addon_file;
	private $addon_name;
	private $addon_version;
	private $addon_author;

	/**
	 * Instance of EDD Plugin Updater.
	 *
	 * @var \EDD_SL_Plugin_Updater
	 */
	private $updater;

	/**
	 * Addon License field Renderer.
	 *
	 * @var \EmailLog\Core\UI\Addon\AddonLicenseRenderer
	 */
	private $renderer;

	/**
	 * AddonLicenseHandler constructor.
	 *
	 * @param string                                       $addon_file Add-on main file.
	 * @param \EmailLog\Core\UI\Addon\AddonLicenseRenderer $renderer   Addon License Renderer.
	 */
	public function __construct( $addon_file, $renderer ) {
		$this->addon_file = $addon_file;
		$this->renderer = $renderer;
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

		$this->renderer->set_addon_data( $addon_name, $addon_version );
	}

	/**
	 * Set up hooks and load the license handler.
	 * This method is called on `wp-loaded` hook.
	 */
	public function load() {
		add_action( 'admin_init', array( $this, 'setup_updater' ) );

		$this->renderer->load();
	}

	/**
	 * Setup up Add-on auto-updater using EDD library.
	 */
	public function setup_updater() {
		$email_log = email_log();
		$license_key = $email_log->get_licenser()->get_addon_license_key( $this->addon_name );

		if ( ! class_exists( 'EDD_SL_Plugin_Updater' ) ) {
			require_once $email_log->get_plugin_path() . 'include/libraries/EDD_SL_Plugin_Updater.php';
		}

		$this->updater = new \EDD_SL_Plugin_Updater( self::STORE_URL, $this->addon_file, array(
				'version'   => $this->addon_version,
				'license'   => $license_key,
				'item_name' => $this->addon_name,
				'author'    => $this->addon_author,
			)
		);
	}
}
