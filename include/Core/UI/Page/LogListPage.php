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
	 * Delete Log Nonce Field.
	 */
	const DELETE_LOG_NONCE_FIELD = 'el-delete-email-log-nonce';

	/**
	 * Delete Log Action.
	 */
	const DELETE_LOG_ACTION = 'el-delete-email-log';

	/**
	 * Setup hooks.
	 */
	public function load() {
		parent::load();

		add_filter( 'set-screen-option', array( $this, 'save_screen_options' ), 10, 3 );

		add_action( 'wp_ajax_display_email_message', array( $this, 'display_email_message_callback' ) );

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
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' ),
			'dashicons-email-alt',
			26
		);

		$this->page = add_submenu_page(
			self::PAGE_SLUG,
			__( 'View Logs', 'bulk-delete' ),
			__( 'View Logs', 'bulk-delete' ),
			'manage_options',
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
				wp_nonce_field( self::DELETE_LOG_ACTION, self::DELETE_LOG_NONCE_FIELD );
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
	 * Delete log entires by id.
	 *
	 * @param array|string $ids Ids of log entires to delete.
	 */
	public function delete_logs_by_id( $ids ) {
		$this->check_nonce();

		if ( is_array( $ids ) ) {
			$ids          = array_map( 'absint', $ids );
			$selected_ids = implode( ',', $ids );
		} else {
			$selected_ids = absint( $ids );
		}

		$logs_deleted = $this->get_table_manager()->delete_logs_by_id( $selected_ids );
		$this->render_log_deleted_notice( $logs_deleted );
	}

	/**
	 * Delete all log entires.
	 */
	public function delete_all_logs() {
		$this->check_nonce();

		$logs_deleted = $this->get_table_manager()->delete_all_logs();
		$this->render_log_deleted_notice( $logs_deleted );
	}

	/**
	 * Verify nonce.
	 */
	protected function check_nonce() {
		$nonce = $_REQUEST[ self::DELETE_LOG_NONCE_FIELD ];

		if ( ! wp_verify_nonce( $nonce, self::DELETE_LOG_ACTION ) ) {
			wp_die( 'Cheating, Huh? ' );
		}
	}

	/**
	 * Get nonce args.
	 *
	 * @return array Nonce args.
	 */
	public function get_nonce_args() {
		return array(
			self::DELETE_LOG_NONCE_FIELD => wp_create_nonce( self::DELETE_LOG_ACTION ),
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
	 * Render Logs deleted notice.
	 *
	 * @param int|False $logs_deleted Number of entires deleted, False otherwise.
	 */
	protected function render_log_deleted_notice( $logs_deleted ) {
		$message = __( 'There was some problem in deleting the email logs', 'email-log' );
		$type    = 'error';

		if ( absint( $logs_deleted ) > 0 ) {
			$message = sprintf( _n( '1 email log deleted.', '%s email logs deleted', $logs_deleted, 'email-log' ), $logs_deleted );
			$type    = 'updated';
		}

		add_settings_error(
			self::PAGE_SLUG,
			'deleted-email-logs',
			$message,
			$type
		);
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
	 * AJAX callback for displaying email content.
	 *
	 * @since 1.6
	 */
	public function display_email_message_callback() {
		if ( current_user_can( 'manage_options' ) ) {
			$message = '';

			$id = absint( $_GET['log_id'] );
			if ( $id > 0 ) {
				$message = $this->get_table_manager()->get_log_message( $id );
			}

			echo wpautop( $message );
		}

		die(); // this is required to return a proper result
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

		wp_enqueue_style( 'jquery-ui', $plugin_dir_url . 'assets/vendor/jquery-ui/themes/smoothness/jquery-ui.min.css', array(), '1.12.1' );
		wp_enqueue_style( 'el-view-logs-css', $plugin_dir_url . 'assets/css/admin/view-logs.css', array( 'jquery-ui' ), $email_log->get_version() );

		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_enqueue_script( 'el-view-logs-js', $plugin_dir_url . 'assets/js/admin/view-logs.js', array( 'jquery-ui-datepicker' ), $email_log->get_version(), true );
	}
}
