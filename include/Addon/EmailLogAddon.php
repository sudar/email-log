<?php namespace EmailLog\Addon;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Base Email Log Addon.
 *
 * @since 2.0.0
 */
abstract class EmailLogAddon {

	protected $addon_file;
	protected $addon_name = '';
	protected $addon_version = '';
	protected $addon_author = 'Sudar Muthu';

	/**
	 * Addon License Handler.
	 *
	 * @var EmailLog\Addon\AddonLicenseHandler.
	 */
	private $license_handler;

	/**
	 * Initialize add-on data.
	 *
	 * @access protected
	 * @return void
	 */
	abstract protected function initialize();

	/**
	 * Construct a new EmailLogAddon instance.
	 *
	 * @param string                             $addon_file      Addon main file.
	 * @param EmailLog\Addon\AddonLicenseHandler $license_handler Addon License Handler.
	 */
	public function __construct( $addon_file, $license_handler ) {
		$this->addon_file      = $addon_file;
		$this->license_handler = $license_handler;

		$this->initialize();
	}

	/**
	 * Load the add-on and setup hooks.
	 * This method is called on `wp-loaded` hook.
	 */
	public function load() {
		$this->license_handler->set_addon_data( $this->addon_name, $this->addon_version, $this->addon_author );
		$this->license_handler->load();
	}
}
