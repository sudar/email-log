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
	 *
	 * @since 2.4.0 Added div.el-addons-list
	 *
	 * TODO: Move the inline styles to the stylesheet.
	 * TODO: Make the content within el-summary-list-right element dynamic.
	 */
	public function render() {
		$email_log  = email_log();
		$logs_count = $email_log->table_manager->get_logs_count();
		?>
		<style>
			#email_log_dashboard_widget .inside {
				margin: 0;
				padding-bottom: 0;
			}

			.el-summary-list-left {
				min-width: 150px;
				margin-right: 5px;
				color: #72777c;
			}

			.el-addons-list ul {
				margin: 0 -12px;
			}

			.el-addons-list ul li {
				padding-left: 12px;
				padding-right: 12px;
			}

			.el-addons-list ul li:first-child {
				padding-top: 12px;
				box-shadow: inset 0 1px 0 rgba(0, 0, 0, 0.06);
			}

			#el-inactive-addons-list {
				color: #72777c;
			}

			#email_log_dashboard_widget .subsubsub {
				margin: 0 0;
				padding-bottom: 4px;
			}
		</style>
		<div id="el-summary" class="activity-block">
			<h3><?php _e( 'Summary', 'email-log' ); ?></h3>
				<ul>
					<li>
						<span class="el-summary-list-left"><?php _e( 'Total number of emails logged' ,	'email-log'	);
						?></span>
						<span class="el-summary-list-right"><?php echo number_format( absint(
							$logs_count ), 0, ',', ','
							); ?></span>
					</li>
					<li>
						<span class="el-summary-list-left"><?php esc_html_e( 'Next Auto delete logs cron run', 'email-log' ); ?></span>
						<span class="el-summary-list-right">August 2, 2019 2:01 pm</span>
					</li>
				</ul>

			<?php // Util\render_auto_delete_logs_next_run_schedule(); ?>
		</div>
		<div id="el-active-addons-list" class="el-addons-list activity-block">
			<h3>Installed & Active</h3>
			<ul>
				<?php
					foreach( $this->get_active_addons_list() as $addon ) :
					?>
						<li><?php printf( esc_html( $addon ) ); ?></li>
				<?php
					endforeach;
				?>
			</ul>
		</div>
		<div id="el-inactive-addons-list" class="el-addons-list activity-block">
			<h3>Installed & In-active</h3>
			<ul>
				<?php
				foreach( $this->get_inactive_addons_list() as $addon ) :
					?>
					<li><?php printf( esc_html( $addon ) ); ?></li>
				<?php
				endforeach;
				?>
			</ul>
		</div>
		<div class="activity-block">
			<ul class="subsubsub" style="float: none">
				<li><?php printf( __( '<a href="%s">Email Logs</a>', 'email-log' ), 'admin.php?page=email-log' ); ?> <span style="color: #ddd"> | </span></li>
				<li><?php printf( __( '<a href="%s">Settings</a>', 'email-log' ), 'admin.php?page=email-log-settings' ); ?> <span style="color: #ddd"> | </span></li>
				<li><?php printf( __( '<a href="%s">Addons</a>', 'email-log' ), 'admin.php?page=email-log-addons' ); ?></li>
			</ul>
		</div>
		<?php
	}

	/**
	 * Gets the list of active addons.
	 *
	 * @since 2.4.0
	 *
	 * TODO: The data from the licenser is cached as transients. Cross check before returning.
	 *
	 * @return array
	 */
	public function get_active_addons_list() {
		$inactive_adddons = $this->get_inactive_addons_list();
		$addons           = array();
		try {
			foreach ( email_log()->get_licenser()->get_addon_list()->get_addons() as $addon ) {
				if ( ! property_exists( get_class( $addon ), 'name' ) ) {
					continue;
				}
				$addons[] = $addon->name;
			}
		} catch ( \Exception $exception ) {
			// TODO: How should we handle errors?
		}

		return array_diff( $addons, $inactive_adddons );
	}

	/**
	 * Gets the list of inactive addons.
	 *
	 * @since 2.4.0
	 *
	 * TODO: The data from the licenser is cached as transients. Cross check before returning.
	 *
	 * @return array
	 */
	public function get_inactive_addons_list() {
		$inactive_addons = array();
		try {
			foreach ( email_log()->get_licenser()->get_addon_list()->get_inactive_addons() as
				$addon ) {
				if ( ! property_exists( get_class( $addon ), 'name' ) ) {
					continue;
				}
				$inactive_addons[] = $addon->name;
			}
		} catch ( \Exception $exception ) {
			// TODO: How should we handle errors?
		}

		return $inactive_addons;
	}
}
