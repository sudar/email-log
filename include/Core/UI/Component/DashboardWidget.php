<?php namespace EmailLog\Core\UI\Component;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Enhance Admin UI and add links about EmailLog in the following places.
 * - Plugin List page.
 * - Footer for all EmailLog pages.
 *
 * @since 2.0.0
 */
class DashboardWidget {

	/**
	 * Plugin file name.
	 *
	 * @var string
	 */
	protected $plugin_file;

	/**
	 * Plugin basename.
	 *
	 * @var string
	 */
	protected $plugin_basename;

	/**
	 * Initialize the component and store the plugin basename.
	 *
	 * @param string|null $file Plugin file.
	 */
	public function __construct( $file = null ) {
		if ( null === $file ) {
			$email_log = email_log();
			$file      = $email_log->get_plugin_file();
		}

		$this->plugin_file     = $file;
		$this->plugin_basename = plugin_basename( $file );
	}

	/**
	 * Setup hooks.
	 *
	 * @inheritdoc
	 */
	public function load() {
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );
	}

	/**
	 * Adds the dashboard widget to display Email Log activity.
	 */
	public function add_dashboard_widget() {

		wp_add_dashboard_widget(
			'email_log_dashboard_widget',
			__( 'Email Log Activity', 'email-log' ),
			array( $this, 'display_email_log_activity' )
		);
	}

	/**
	 * Outputs the contents on the Dashboard Widget.
	 */
	public function display_email_log_activity() {
		$email_log  = email_log();
		$logs_count = $email_log->table_manager->fetch_logs_count();

		ob_start();
		?>
		<p>Total number of emails logged: <strong><?php echo $logs_count; ?></strong></p>
		<p><a href="">Click here</a> to view Email Logs.</p>
		<?php
		echo ob_get_clean();
	}
}