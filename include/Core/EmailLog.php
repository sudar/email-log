<?php namespace EmailLog\Core;

use EmailLog\Core\DB\TableManager;

/**
 * The main plugin class.
 *
 * @since Genesis
 */
class EmailLog {

	/**
	 * Version number.
	 *
	 * @since Genesis
	 * @var const VERSION
	 */
	const VERSION = '1.9.1';

	/**
	 * Flag to track if the plugin is loaded.
	 *
	 * @since 2.0
	 * @var bool
	 */
	private $loaded;

	/**
	 * @var string Plugin file path.
	 *
	 * @since 2.0
	 */
	private $plugin_file;

	/**
	 * Filesystem directory path where translations are stored.
	 *
	 * @since 2.0
	 * @var string $translations_path
	 */
	public $translations_path;

	/**
	 * @var object TableManager.
	 *
	 * @since 2.0
	 */
	public $table_manager;

	/**
	 * @var object EmailLogger
	 *
	 * @since 2.0
	 */
	public $logger;

	/**
	 * @var object UIManager.
	 *
	 * @since 2.0
	 */
	public $ui_manager;

	// JS Stuff
	const JS_HANDLE                = 'email-log';

	// coloumn hooks
	const HOOK_LOG_COLUMNS         = 'email_log_manage_log_columns';
	const HOOK_LOG_DISPLAY_COLUMNS = 'email_log_display_log_columns';

	/**
	 * Initialize the plugin.
	 */
	public function __construct( $file ) {
		$this->plugin_file = $file;
		$this->translations_path = dirname( plugin_basename( $this->plugin_file ) ) . '/languages/' ;
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

		$this->loaded = true;
	}
}
