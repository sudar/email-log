<?php namespace EmailLog\Addon;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Handles upsell messages.
 */

 class Upseller{

   	/**
	 * Renders Upsell message for Auto delete logs add-on.
	 *
	 * @since 2.4.0
	 */
	public function render_auto_delete_logs_addon_upsell_message() {
        echo "Heeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeeee";
		if ( ! \PAnD::is_admin_notice_active( 'disable-upsell-notice-forever' ) ) {
			return;
		}
		$email_log  = email_log();
		$logs_count = $email_log->table_manager->get_logs_count();
		//if ( $logs_count > 5 ) {
		?>
		<div data-dismissible="disable-upsell-notice-forever" class="updated notice notice-success is-dismissible">
			<p><?php _e( 'The Auto Delete Logs add-on allows you to automatically delete logs based on a schedule. <a href="https://wpemaillog.com/addons/auto-delete-logs/?utm_campaign=Upsell&utm_medium=wpadmin&utm_source=log-list&utm_content=dl" target="_blank">Buy now</a>', 'email-log' ); ?></p>
		</div>
		<?php
		//}
	}

 }