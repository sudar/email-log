<?php namespace EmailLog\Core\UI\Page;

use EmailLog\Core\UI\Component\EmailLogSystemInfo;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * System Info Page.
 *
 * This page displays information about the current WordPress install that can be used in support requests.
 *
 * @since 2.3.0
 */
class SystemInfoPage extends BasePage {
	const PAGE_SLUG = 'email-log-system-info';

	/**
	 * Capability to Manage system info.
	 */
	const CAPABILITY = 'manage_email_logs';

	/**
	 * SystemInfo class.
	 *
	 * @var EmailLogSystemInfo
	 */
	protected $system_info;

	public function load() {
		parent::load();

		add_action( 'el-download-system-info', array( $this, 'download_system_info' ) );

		$this->system_info = new EmailLogSystemInfo( 'email-log' );
		$this->system_info->load();
	}

	public function register_page() {
		$this->page = add_submenu_page(
			LogListPage::PAGE_SLUG,
			__( 'System Info', 'email-log' ),
			__( 'System Info', 'email-log' ),
			self::CAPABILITY,
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);

		add_action( "load-{$this->page}", array( $this, 'render_help_tab' ) );

		/**
		 * Fires before loading Sytem Info page.
		 *
		 * @since 2.3.0
		 *
		 * @param string $page Page slug.
		 */
		do_action( 'el_load_system_info_page', $this->page );
	}

	/**
	 * Render the page.
	 */
	public function render_page() {
		?>

		<form method="post">
			<div class="updated">
				<p>
					<strong>
						<?php _e( 'Please include this information when posting support requests.', 'email-log' ); ?>
					</strong>
				</p>
			</div>

			<?php if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) : ?>
				<div class="notice notice-warning">
					<p><strong>
						<?php printf( __( 'DISABLE_WP_CRON is <a href="%s" rel="noopener" target="_blank">enabled</a>. This prevents scheduler from running.', 'email-log' ), 'https://codex.wordpress.org/Editing_wp-config.php#Disable_Cron_and_Cron_Timeout' ); ?>
					</strong></p>
				</div>
			<?php endif; ?>

			<div class="wrap">
				<h1><?php _e( 'Email Log  - System Info', 'email-log' ); ?></h1>

				<?php
					$this->system_info->render();
				?>

				<p class="submit">
					<input type="hidden" name="el-action" value="el-download-system-info">
					<?php
						wp_nonce_field( 'el-download-system-info', 'el-download-system-info_nonce', false );
						submit_button( __( 'Download System Info File', 'email-log' ), 'primary', 'el-download-system-info', false );
					?>
				</p>
			</div>
		</form>

		<?php
		$this->render_page_footer();
	}

	/**
	 * Download System info file.
	 */
	public function download_system_info() {
		$this->system_info->download_as_file();
	}
}
