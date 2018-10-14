<?php namespace EmailLog\Core\UI\Component;

use EmailLog\Core\Loadie;
use EmailLog\Util;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/*
 * Widget that displays email log information in dashboard.
 *
 * @since 2.2.0
 */
class DashboardWidget implements Loadie {

	/**
	 * Setup hooks.
	 *
	 * @inheritdoc
	 */
	public function load() {
		add_action( 'wp_dashboard_setup', array( $this, 'register' ) );
	}

	/**
	 * Adds the dashboard widget to display Email Log activity.
	 */
	public function register() {
		wp_add_dashboard_widget(
			'email_log_dashboard_widget',
			__( 'Email Logs Summary', 'email-log' ),
			array( $this, 'render' )
		);
	}

	/**
	 * Outputs the contents on the Dashboard Widget.
	 */
	public function render() {
		$email_log  = email_log();
		$logs_count = $email_log->table_manager->get_logs_count();
		?>

		<p>
			<?php _e( 'Total number of emails logged' , 'email-log' ); ?>: <strong><?php echo number_format( absint( $logs_count ), 0, ',', ',' ); ?></strong>
		</p>

		<?php Util\render_auto_delete_logs_next_run_schedule(); ?>

		<ul class="subsubsub" style="float: none">
			<li><?php printf( __( '<a href="%s">Email Logs</a>', 'email-log' ), 'admin.php?page=email-log' ); ?> <span style="color: #ddd"> | </span></li>
			<li><?php printf( __( '<a href="%s">Settings</a>', 'email-log' ), 'admin.php?page=email-log-settings' ); ?> <span style="color: #ddd"> | </span></li>
			<li><?php printf( __( '<a href="%s">Addons</a>', 'email-log' ), 'admin.php?page=email-log-addons' ); ?></li>
		</ul>

		<?php
	}
}
