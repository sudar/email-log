<?php namespace EmailLog\Addon;

use EmailLog\Core\Loadie;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Base Email Log Addon.
 *
 * @since 2.0.0
 */
abstract class EmailLogAddon implements Loadie {

	protected $addon_file;
	protected $addon_name    = '';
	protected $addon_version = '';
	protected $addon_author  = 'Sudar Muthu';

	/**
	 * Addon Updater.
	 *
	 * @var \EmailLog\Addon\AddonUpdater
	 */
	private $updater;

	/**
	 * Initialize add-on data.
	 *
	 * @access protected
	 *
	 * @return void
	 */
	abstract protected function initialize();

	/**
	 * Construct a new EmailLogAddon instance.
	 *
	 * @param string                            $addon_file Addon main file.
	 * @param \EmailLog\Addon\AddonUpdater|null $updater    Addon Updater.
	 */
	public function __construct( $addon_file, $updater = null ) {
		$this->addon_file = $addon_file;
		$this->updater    = $updater;

		$this->initialize();
	}

	/**
	 * Load the add-on and setup hooks.
	 */
	public function load() {
		if ( \EmailLog\Util\is_admin_non_ajax_request() ) {
			$email_log = email_log();

			if ( ! $email_log->is_plugin_api_overridden() ) {
				$override_plugin_api = new \EmailLog\Core\Request\OverridePluginAPI();
				$override_plugin_api->load();

				$email_log->plugin_api_overridden();
			}
		}

		if ( is_null( $this->updater ) ) {
			return;
		}

		$this->updater->set_addon_data( $this->addon_name, $this->addon_version, $this->addon_author );
		$this->updater->load();
	}
}
