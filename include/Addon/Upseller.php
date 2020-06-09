<?php namespace EmailLog\Addon;

use EmailLog\Core\Loadie;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Handles upsell messages.
 */

 class Upseller implements Loadie {

	/**
	 * Load all hooks.
	 *
	 * @inheritdoc
	 */
	public function load() {
		add_action( 'el_before_logs_list_table', array( $this, 'auto_delete_logs_addon_upsell_message_loglist_page' ) );
		add_action( 'el_auto_delete_logs_addon_upsell_message', array( $this, 'auto_delete_logs_addon_upsell_message_settings_page' ) );
	}

   	/**
	 * Renders Upsell message for Auto delete logs add-on in Log list page.
	 *
	 * @since 2.4.0
	 */
	public function auto_delete_logs_addon_upsell_message_loglist_page() {
		$email_log  = email_log();
		$logs_count = $email_log->table_manager->get_logs_count();
		if ( $logs_count > 5000 ) {
			if ( ! \PAnD::is_admin_notice_active( 'disable-upsell-notice-forever' ) ) {
				return;
			}
		?>
		<div data-dismissible="disable-upsell-notice-forever" class="updated notice notice-success is-dismissible">
			<p><?php _e( 'The Auto Delete Logs add-on allows you to automatically delete logs based on a schedule. <a href="https://wpemaillog.com/addons/auto-delete-logs/?utm_campaign=Upsell&utm_medium=wpadmin&utm_source=log-list&utm_content=dl" target="_blank">Buy now</a>', 'email-log' ); ?></p>
		</div>
		<?php
		}
	}

	/**
	 * Renders Upsell message for Auto delete logs add-on in Settings page.
	 *
	 * @since 2.4.0
	 */
	public function auto_delete_logs_addon_upsell_message_settings_page() {
		?>
		<div>
			<p><?php _e( 'The Auto Delete Logs add-on allows you to automatically delete logs based on a schedule. <a href="https://wpemaillog.com/addons/auto-delete-logs/?utm_campaign=Upsell&utm_medium=wpadmin&utm_source=settings&utm_content=dl" target="_blank">Buy now</a>', 'email-log' ); ?></p>
		</div>
		<?php
	}

 }
