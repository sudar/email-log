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

	/**
	 * Admin screen object.
	 *
	 * @since Genesis
	 * @access private
	 * @var string $include_path
	 */
	private $admin_screen;

	/**
	 * Page slug to be used in admin dashboard hyperlinks.
	 *
	 * @since Genesis
	 * @var const PAGE_SLUG
	 */
	const PAGE_SLUG                = 'email-log';

	/**
	 * String value to generate nonce.
	 *
	 * @since Genesis
	 * @var const DELETE_LOG_NONCE_FIELD
	 */
	const DELETE_LOG_NONCE_FIELD   = 'sm-delete-email-log-nonce';

	/**
	 * String value to generate nonce.
	 *
	 * @since Genesis
	 * @var const DELETE_LOG_ACTION
	 */
	const DELETE_LOG_ACTION        = 'sm-delete-email-log';

	// JS Stuff
	const JS_HANDLE                = 'email-log';

	//hooks
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

		// Load localization domain.
		load_plugin_textdomain( 'email-log', false, $this->translations_path );

		// Register hooks.
		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );

		// Register Filter.
		add_filter( 'set-screen-option', array( $this, 'save_screen_options' ), 10, 3 );

		// Add our ajax call.
		add_action( 'wp_ajax_display_content', array( $this, 'display_content_callback' ) );

		$this->table_manager->load();
		$this->logger->load();
		$this->ui_manager->load();

		$this->loaded = true;
	}

	/**
	 * Registers the settings page.
	 * TODO: Move to UI namespace
	 *
	 * @since Genesis
	 */
	public function register_settings_page() {
		// Save the handle to your admin page - you'll need it to create a WP_Screen object
		$this->admin_page = add_submenu_page( 'tools.php', __( 'Email Log', 'email-log' ), __( 'Email Log', 'email-log' ), 'manage_options', self::PAGE_SLUG , array( $this, 'display_logs' ) );

		add_action( "load-{$this->admin_page}", array( $this, 'create_settings_panel' ) );
	}

	/**
	 * Displays the stored email log.
	 * TODO: Move to UI namespace
	 *
	 * @since Genesis
	 */
	public function display_logs() {
		add_thickbox();

		$this->logs_table->prepare_items( $this->get_per_page() );
		?>
		<div class="wrap">
			<h2><?php _e( 'Email Logs', 'email-log' );?></h2>
			<?php
			if ( isset( $this->logs_deleted ) && $this->logs_deleted != '' ) {
				$logs_deleted = intval( $this->logs_deleted );

				if ( $logs_deleted > 0 ) {
					echo '<div class="updated"><p>' . sprintf( _n( '1 email log deleted.', '%s email logs deleted', $logs_deleted, 'email-log' ), $logs_deleted ) . '</p></div>';
				} else {
					echo '<div class="updated"><p>' . __( 'There was some problem in deleting the email logs' , 'email-log' ) . '</p></div>';
				}
				unset( $this->logs_deleted );
			}
			?>
			<form id="email-logs-search" method="get">
				<input type="hidden" name="page" value="<?php echo self::PAGE_SLUG; ?>" >
				<?php
				$this->logs_table->search_box( __( 'Search Logs', 'email-log' ), 'search_id' );
				?>
			</form>

			<form id="email-logs-filter" method="get">
				<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
				<?php
				wp_nonce_field( self::DELETE_LOG_ACTION, self::DELETE_LOG_NONCE_FIELD );
				$this->logs_table->display();
				?>
			</form>
		</div>
		<?php
		/**
		 * Action to add additional content to email log admin footer.
		 *
		 * @since 1.8
		 */
		do_action( 'el_admin_footer' );

		// Display credits in Footer
		add_action( 'in_admin_footer', array( $this, 'add_footer_links' ) );
	}

	/**
	 * Adds settings panel for the plugin.
	 * TODO: Move to UI namespace
	 *
	 * @since Genesis
	 */
	public function create_settings_panel() {

		/**
		 * Create the WP_Screen object against your admin page handle
		 * This ensures we're working with the right admin page
		 */
		$this->admin_screen = \WP_Screen::get( $this->admin_page );

		/**
		 * Content specified inline
		 */
		$this->admin_screen->add_help_tab(
			array(
				'title'    => __( 'About Plugin', 'email-log' ),
				'id'       => 'about_tab',
				'content'  => '<p>' . __( 'Email Log WordPress Plugin, allows you to log all emails that are sent through WordPress.', 'email-log' ) . '</p>',
				'callback' => false,
			)
		);

		// Add help sidebar
		$this->admin_screen->set_help_sidebar(
			'<p><strong>' . __( 'More information', 'email-log' ) . '</strong></p>' .
			'<p><a href = "http://sudarmuthu.com/wordpress/email-log">' . __( 'Plugin Homepage/support', 'email-log' ) . '</a></p>' .
			'<p><a href = "http://sudarmuthu.com/blog">' . __( "Plugin author's blog", 'email-log' ) . '</a></p>' .
			'<p><a href = "http://sudarmuthu.com/wordpress/">' . __( "Other Plugin's by Author", 'email-log' ) . '</a></p>'
		);

		// Add screen options
		$this->admin_screen->add_option(
			'per_page',
			array(
				'label' => __( 'Entries per page', 'email-log' ),
				'default' => 20,
				'option' => 'per_page',
			)
		);

		//Prepare Table of elements
		$this->logs_table = new UI\LogListTable();
	}

	/**
	 * AJAX callback for displaying email content.
	 * TODO: Move to UI namespace
	 *
	 * @since 1.6
	 */
	public function display_content_callback() {
		global $wpdb;

		if ( current_user_can( 'manage_options' ) ) {
			$table_name = $wpdb->prefix . TableManager::LOG_TABLE_NAME;
			$email_id   = absint( $_GET['email_id'] );

			$query   = $wpdb->prepare( 'SELECT * FROM ' . $table_name . ' WHERE id = %d', $email_id );
			$content = $wpdb->get_results( $query );

			echo wpautop( $content[0]->message );
		}

		die(); // this is required to return a proper result
	}

	/**
	 * Saves Screen options.
	 * TODO: Move to UI namespace
	 *
	 * @since Genesis
	 *
	 * @param bool|int $status Screen option value. Default false to skip.
	 * @param string   $option The option name.
	 * @param int      $value  The number of rows to use.
	 * @return bool|int
	 */
	function save_screen_options( $status, $option, $value ) {
		if ( 'per_page' == $option ) {
			return $value;
		} else {
			return $status;
		}
	}

	/**
	 * Gets the per page option.
	 * TODO: Move to UI namespace
	 *
	 * @since Genesis
	 *
	 * @return int Number of logs a user wanted to be displayed in a page.
	 */
	public static function get_per_page() {
		$screen = get_current_screen();
		$option = $screen->get_option( 'per_page', 'option' );

		$per_page = get_user_meta( get_current_user_id(), $option, true );

		if ( empty( $per_page ) || $per_page < 1 ) {
			$per_page = $screen->get_option( 'per_page', 'default' );
		}

		return $per_page;
	}

	/**
	 * Adds Footer links.
	 * TODO: Move to UI namespace
	 *
	 * @since Genesis
	 *
	 * @see Function relied on
	 * @link http://striderweb.com/nerdaphernalia/2008/06/give-your-wordpress-plugin-credit/
	 */
	public function add_footer_links() {
		$plugin_data = get_plugin_data( $this->plugin_file );
		printf( '%1$s ' . __( 'plugin', 'email-log' ) . ' | ' . __( 'Version', 'email-log' ) . ' %2$s | ' . __( 'by', 'email-log' ) . ' %3$s<br />', $plugin_data['Title'], $plugin_data['Version'], $plugin_data['Author'] );
	}
}
