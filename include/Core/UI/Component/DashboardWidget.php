<?php namespace EmailLog\Core\UI\Component;

use EmailLog\Core\Loadie;

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
			<?php _e( 'Total number of emails logged' , 'email-log' ); ?>: <strong><?php echo absint( $logs_count ); ?></strong>
		</p>

		<p>
			<?php printf( __( '<a href="%s">Click here</a> to view Email Logs.', 'email-log' ), 'admin.php?page=email-log' ); ?>
		</p>

		<?php
	}
}
