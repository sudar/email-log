<?php namespace EmailLog\Core;

use EmailLog\Core\DB\TableManager;
use EmailLog\EmailLogAutoloader;

/**
 * The main plugin class.
 *
 * @since Genesis
 */
class EmailLog {

	/**
	 * Plugin Version number.
	 *
	 * @since Genesis
	 *
	 * @var string
	 */
	const VERSION = '2.2.5';

	/**
	 * Email Log Store URL.
	 */
	const STORE_URL = 'https://wpemaillog.com';

	/**
	 * Flag to track if the plugin is loaded.
	 *
	 * @since 2.0
	 * @access private
	 *
	 * @var bool
	 */
	private $loaded = false;

	/**
	 * Plugin file path.
	 *
	 * @since 2.0
	 * @access private
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * Filesystem directory path where translations are stored.
	 *
	 * @since 2.0
	 *
	 * @var string
	 */
	public $translations_path;

	/**
	 * Auto loader.
	 *
	 * @var \EmailLog\EmailLogAutoloader
	 */
	public $loader;

	/**
	 * Database Table Manager.
	 *
	 * @since 2.0
	 *
	 * @var \EmailLog\Core\DB\TableManager
	 */
	public $table_manager;

	/**
	 * Add-on Licenser.
	 * For non-admin requests it will not be set.
	 *
	 * @since 2.0
	 *
	 * @var \EmailLog\Addon\License\Licenser
	 */
	private $licenser = null;

	/**
	 * List of loadies.
	 *
	 * @var Loadie[]
	 */
	private $loadies = array();

	/**
	 * Initialize the plugin.
	 *
	 * @param string             $file          Plugin file.
	 * @param EmailLogAutoloader $loader        EmailLog Autoloader.
	 * @param TableManager       $table_manager Table Manager.
	 */
	public function __construct( $file, $loader, $table_manager ) {
		$this->plugin_file   = $file;
		$this->loader        = $loader;
		$this->table_manager = $table_manager;

		$this->add_loadie( $table_manager );

		$this->translations_path = dirname( plugin_basename( $this->plugin_file ) ) . '/languages/' ;
	}

	/**
	 * Set Licenser.
	 *
	 * @param \EmailLog\Addon\License\Licenser $licenser Add-on Licenser.
	 */
	public function set_licenser( $licenser ) {
		if ( $this->add_loadie( $licenser ) ) {
			$this->licenser = $licenser;
		}
	}

	/**
	 * Get Licenser.
	 *
	 * @return \EmailLog\Addon\License\Licenser|null
	 */
	public function get_licenser() {
		return $this->licenser;
	}

	/**
	 * Add an Email Log Loadie.
	 * The `load()` method of the Loadies will be called when Email Log is loaded.
	 *
	 * @param \EmailLog\Core\Loadie $loadie Loadie to be loaded.
	 *
	 * @return bool False if Email Log is already loaded or if $loadie is not of `Loadie` type. True otherwise.
	 */
	public function add_loadie( $loadie ) {
		if ( $this->loaded ) {
			return false;
		}

		if ( ! $loadie instanceof Loadie ) {
			return false;
		}

		$this->loadies[] = $loadie;

		return true;
	}

	/**
	 * Load the plugin.
	 */
	public function load() {
		if ( $this->loaded ) {
			return;
		}

		load_plugin_textdomain( 'email-log', false, $this->translations_path );

		$this->table_manager->load();

		foreach ( $this->loadies as $loadie ) {
			$loadie->load();
		}

		$this->loaded = true;

		/**
		 * Email Log plugin loaded.
		 *
		 * @since 2.0
		 */
		do_action( 'el_loaded' );
	}

	/**
	 * Return Email Log version.
	 *
	 * @return string Email Log Version.
	 */
	public function get_version() {
		return self::VERSION;
	}

	/**
	 * Return the Email Log plugin directory path.
	 *
	 * @return string Plugin directory path.
	 */
	public function get_plugin_path() {
		return plugin_dir_path( $this->plugin_file );
	}

	/**
	 * Return the Email Log plugin file.
	 *
	 * @since 2.0.0
	 *
	 * @return string Plugin directory path.
	 */
	public function get_plugin_file() {
		return $this->plugin_file;
	}

	/**
	 * Get Email Log Store URL.
	 *
	 * @since 2.0.0
	 *
	 * @return string Store URL
	 */
	public function get_store_url() {
		return self::STORE_URL;
	}
}
