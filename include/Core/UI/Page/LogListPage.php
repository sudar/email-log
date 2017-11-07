<?php namespace EmailLog\Core\UI\Page;

use EmailLog\Core\DB\TableManager;
use EmailLog\Core\UI\ListTable\LogListTable;

/**
 * Log List Page.
 *
 * @since 2.0
 */
class LogListPage extends BasePage {
	/**
	 * @var LogListTable
	 */
	protected $log_list_table;

	/**
	 * Page slug.
	 */
	const PAGE_SLUG = 'email-log';

	/**
	 * Nonce Field.
	 */
	const LOG_LIST_ACTION_NONCE_FIELD = 'el-log-list-nonce-field';

	/**
	 * Nonce name.
	 */
	const LOG_LIST_ACTION_NONCE = 'el-log-list-nonce';

	/**
	 * Capability to manage email logs.
	 *
	 * @since 2.1.0
	 */
	const CAPABILITY = 'manage_email_logs';

	/**
	 * Setup hooks.
	 */
	public function load() {
		parent::load();

		add_filter( 'set-screen-option', array( $this, 'save_screen_options' ), 10, 3 );

		add_action( 'admin_enqueue_scripts', array( $this, 'load_view_logs_assets' ) );
	}

	/**
	 * Register page.
	 *
	 * @inheritdoc
	 */
	public function register_page() {
		add_menu_page(
			__( 'Email Log', 'email-log' ),
			__( 'Email Log', 'email-log' ),
			self::CAPABILITY,
			self::PAGE_SLUG,
			array( $this, 'render_page' ),
			'dashicons-email-alt',
			26
		);

		$this->page = add_submenu_page(
			self::PAGE_SLUG,
			__( 'View Logs', 'email-log'),
			__( 'View Logs', 'email-log'),
			self::CAPABILITY,
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);

		add_action( "load-{$this->page}", array( $this, 'load_page' ) );

		/**
		 * Fires before loading log list page.
		 *
		 * @since 2.0
		 *
		 * @param string $page Page slug.
		 */
		do_action( 'el_load_log_list_page', $this->page );
	}

	/**
	 * Render page.
	 */
	public function render_page() {
		if ( ! current_user_can( self::CAPABILITY ) ) {
			return;
		}

		add_thickbox();

		$this->log_list_table->prepare_items();
		?>
		<div class="wrap">
			<h2><?php _e( 'Email Logs', 'email-log' ); ?></h2>
			<?php settings_errors(); ?>

			<form id="email-logs-search" method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( self::PAGE_SLUG ); ?>">
				<?php $this->log_list_table->search_box( __( 'Search Logs', 'email-log' ), 'search_id' ); ?>
			</form>

			<form id="email-logs-filter" method="get">
				<input type="hidden" name="page" value="<?php echo esc_attr( $_REQUEST['page'] ); ?>"/>
				<?php
				wp_nonce_field( self::LOG_LIST_ACTION_NONCE, self::LOG_LIST_ACTION_NONCE_FIELD );
				$this->log_list_table->display();
				?>
			</form>
		</div>
		<?php
		$this->render_page_footer();
	}

	/**
	 * Load page.
	 */
	public function load_page() {
		$this->render_help_tab();

		// Add screen options
		$this->get_screen()->add_option(
			'per_page',
			array(
				'label'   => __( 'Entries per page', 'email-log' ),
				'default' => 20,
				'option'  => 'per_page',
			)
		);

		$this->log_list_table = new LogListTable( $this );
	}

	/**
	 * Gets the per page option.
	 *
	 * @return int Number of logs a user wanted to be displayed in a page.
	 */
	public function get_per_page() {
		$screen = get_current_screen();
		$option = $screen->get_option( 'per_page', 'option' );

		$per_page = get_user_meta( get_current_user_id(), $option, true );

		if ( empty( $per_page ) || $per_page < 1 ) {
			$per_page = $screen->get_option( 'per_page', 'default' );
		}

		return $per_page;
	}

	/**
	 * Get nonce args.
	 *
	 * @return array Nonce args.
	 */
	public function get_nonce_args() {
		return array(
			self::LOG_LIST_ACTION_NONCE_FIELD => wp_create_nonce( self::LOG_LIST_ACTION_NONCE ),
		);
	}

	/**
	 * Get TableManager instance.
	 *
	 * @return TableManager TableManager instance.
	 */
	public function get_table_manager() {
		$email_log = email_log();

		return $email_log->table_manager;
	}

	/**
	 * Saves Screen options.
	 *
	 * @since Genesis
	 *
	 * @param bool|int $status Screen option value. Default false to skip.
	 * @param string   $option The option name.
	 * @param int      $value  The number of rows to use.
	 *
	 * @return bool|int
	 */
	public function save_screen_options( $status, $option, $value ) {
		if ( 'per_page' == $option ) {
			return $value;
		} else {
			return $status;
		}
	}

	/**
	 * Loads assets on the Log List page.
	 *
	 * @since 2.0.0
	 *
	 * @param string $hook The current admin page.
	 */
	public function load_view_logs_assets( $hook ) {
		// Don't load assets if not View Logs page.
		if ( 'toplevel_page_email-log' !== $hook ) {
			return;
		}

		$email_log      = email_log();
		$plugin_dir_url = plugin_dir_url( $email_log->get_plugin_file() );

		wp_enqueue_style( 'jquery-ui-css', $plugin_dir_url . 'assets/vendor/jquery-ui/themes/base/jquery-ui.min.css', array(), '1.12.1' );
		wp_enqueue_style( 'el-view-logs-css', $plugin_dir_url . 'assets/css/admin/view-logs.css', array( 'jquery-ui-css' ), $email_log->get_version() );

		wp_register_script( 'jquery-ui', $plugin_dir_url . 'assets/vendor/jquery-ui/jquery-ui.min.js', array( 'jquery' ), '1.12.1', true );
		wp_register_script( 'insertionQ', $plugin_dir_url . 'assets/vendor/insertionQuery/insQ.min.js', array( 'jquery' ), '1.0.4', true );
		wp_enqueue_script( 'jquery-ui-datepicker' );

		wp_enqueue_script( 'el-view-logs', $plugin_dir_url . 'assets/js/admin/view-logs.js', array( 'jquery-ui', 'jquery-ui-datepicker', 'insertionQ' ), $email_log->get_version(), true );
	}
}
