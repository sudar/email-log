<?php namespace EmailLog\Addon;

use EmailLog\Core\Loadie;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Upsells add-ons by displaying links to add-ons with context in different parts of admin interface.
 *
 * @since 2.4.0
 */
class Upseller implements Loadie {

	// phpcs:ignore Squiz.Commenting.FunctionComment.Missing
	public function load() {
		if ( class_exists( 'PAnD' ) ) {
			add_action( 'admin_init', [ 'PAnD', 'init' ] );
		}

		add_action( 'el_before_logs_list_table', [ $this, 'upsell_more_fields_addon_in_log_list_page' ] );

		add_action( 'el_before_logs_list_table', [ $this, 'upsell_auto_delete_logs_in_log_list_page' ] );
		add_action( 'el_after_db_size_notification_setting', [ $this, 'upsell_auto_delete_logs_in_settings_page' ] );
	}

	/**
	 * Renders Upsell message for More Fields add-on in the log list page.
	 *
	 * @since 2.2.5
	 */
	public function upsell_more_fields_addon_in_log_list_page() {
		echo '<span id = "el-pro-msg">';
		_e( 'Additional fields are available through More Fields add-on. ', 'email-log' );

		if ( $this->is_bundle_license_valid() ) {
			echo '<a href="admin.php?page=email-log-addons">';
			_e( 'Install it', 'email-log' );
			echo '</a>';
		} else {
			echo '<a rel="noopener" target="_blank" href="https://wpemaillog.com/addons/more-fields/?utm_campaign=Upsell&utm_medium=wpadmin&utm_source=inline&utm_content=mf" style="color:red">';
			_e( 'Buy Now', 'email-log' );
			echo '</a>';
		}

		echo '</span>';
	}

	/**
	 * Renders Upsell message for Auto delete logs add-on in Log list page
	 * if the number of logs is greater than 5000.
	 *
	 * @param int $total_logs Total number of logs.
	 *
	 */
	public function upsell_auto_delete_logs_in_log_list_page( $total_logs ) {
		if ( $total_logs < 5000 ) {
			return;
		}

		if ( $this->is_addon_active( 'Auto Delete Logs' ) ) {
			return;
		}

		if ( ! class_exists( 'PAnD' ) || ! \PAnD::is_admin_notice_active( 'disable-DL-upsell-notice-5000' ) ) {
			return;
		}
		?>

		<div data-dismissible="disable-DL-upsell-notice-5000" class="notice notice-warning is-dismissible">
			<p>
				<?php
				/* translators: 1 Auto Delete Logs add-on name.  */
				printf(
					__( 'You have more than 5000 email logs in the database. You can use our %1s add-on to automatically delete logs as the DB size grows.', 'email-log' ),
					'<a href="https://wpemaillog.com/addons/auto-delete-logs/?utm_campaign=Upsell&utm_medium=wpadmin&utm_source=log-list&utm_content=dl">Auto Delete Logs</a>'
				);
				?>
			</p>
		</div>

		<?php
	}

	/**
	 * Renders Upsell message for Auto delete logs add-on in Settings page.
	 */
	public function upsell_auto_delete_logs_in_settings_page() {
		if ( $this->is_addon_active( 'Auto Delete Logs' ) ) {
			return;
		}

		?>
		<p>
			<em>
				<?php
				printf(
					__( 'You can also automatically delete logs if the database size increases using our %1s add-on.', 'email-log' ),
					'<a href="https://wpemaillog.com/addons/auto-delete-logs/?utm_campaign=Upsell&utm_medium=wpadmin&utm_source=settings&utm_content=dl" target="_blank">Auto Delete Logs</a>'
				);
				?>
			</em>
		</p>
		<?php
	}

	/**
	 * Is an add-on active?
	 *
	 * @param string $addon_name Add-on name.
	 *
	 * @return bool True if add-on is present and is installed, false otherwise.
	 */
	protected function is_addon_active( $addon_name ) {
		$licenser = email_log()->get_licenser();

		if ( $licenser->is_bundle_license_valid() ) {
			return true;
		}

		return $licenser->is_addon_active( $addon_name );
	}

	/**
	 * Has valid bundle license?
	 *
	 * @return bool True if bundle license is valid, false otherwise.
	 */
	protected function is_bundle_license_valid() {
		$licenser = email_log()->get_licenser();

		if ( $licenser->is_bundle_license_valid() ) {
			return true;
		}

		return false;
	}
}
