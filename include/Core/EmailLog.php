<?php namespace EmailLog\Core;

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
	 * @var string
	 */
	const VERSION = '2.0.0';

	/**
	 * Flag to track if the plugin is loaded.
	 *
	 * @since 2.0
	 * @access private
	 * @var bool
	 */
	private $loaded = false;

	/**
	 * Plugin file path.
	 *
	 * @since 2.0
	 * @access private
	 * @var string
	 */
	private $plugin_file;

	/**
	 * Filesystem directory path where translations are stored.
	 *
	 * @since 2.0
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
	 * @var \EmailLog\Core\DB\TableManager
	 */
	public $table_manager;

	/**
	 * Email Logger.
	 *
	 * @since 2.0
	 * @var \EmailLog\Core\EmailLogger
	 */
	public $logger;

	/**
	 * UI Manager.
	 *
	 * @since 2.0
	 * @var \EmailLog\Core\UI\UIManager
	 */
	public $ui_manager;

	/**
	 * Dependency Enforce.
	 *
	 * @var \EmailLog\Addon\DependencyEnforcer
	 */
	public $dependency_enforcer;

	/**
	 * List of subscribers.
	 *
	 * @var array
	 */
	private $subscribers = array();

	/**
	 * Initialize the plugin.
	 *
	 * @param string $file Plugin file.
	 */
	public function __construct( $file ) {
		$this->plugin_file = $file;
		$this->translations_path = dirname( plugin_basename( $this->plugin_file ) ) . '/languages/' ;
	}

	/**
	 * Add an Email Log Subscriber.
	 * The `load()` method of the subscribers will be called when Email Log is loaded.
	 *
	 * @param \EmailLog\Core\EmailLogSubscriber $subscriber Subscriber to be loaded.
	 *
	 * @return bool False if Email Log is already loaded or if subscriber is not of `EmailLogSubscriber` type. True otherwise.
	 */
	public function add_subscriber( $subscriber ) {
		if ( $this->loaded ) {
			return false;
		}

		if ( ! $subscriber instanceof EmailLogSubscriber ) {
			return false;
		}

		$this->subscribers[] = $subscriber;

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
		$this->logger->load();
		$this->ui_manager->load();
		$this->dependency_enforcer->load();

		foreach ( $this->subscribers as $subscriber ) {
			$subscriber->load();
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
}
