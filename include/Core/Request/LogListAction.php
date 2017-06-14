<?php namespace EmailLog\Core\Request;

use EmailLog\Core\Loadie;

/**
 * Actions performed in Log List.
 *
 * @since 2.0.0
 */
class LogListAction implements Loadie {

	/**
	 * Setup actions.
	 *
	 * @inheritdoc
	 */
	public function load() {
		add_action( 'wp_ajax_el-log-list-view-message', array( $this, 'view_log_message' ) );

		add_action( 'el-log-list-delete', array( $this, 'delete_logs' ) );
		add_action( 'el-log-list-delete-all', array( $this, 'delete_all_logs' ) );
	}

	/**
	 * AJAX callback for displaying email content.
	 *
	 * @since 1.6
	 */
	public function view_log_message() {
		/**
		 * Filters the User capability to View Email Log content.
		 *
		 * Refer User Capabilities at
		 * @link https://codex.wordpress.org/Roles_and_Capabilities#Capabilities
		 *
		 * @since 2.0.0
		 *
		 * @param string $user_capability User capability to view Log content.
		 */
		$view_email_log_capability = apply_filters( 'el_view_email_log_capability', 'manage_options' );

		if ( current_user_can( $view_email_log_capability ) ) {
			$id = absint( $_GET['log_id'] );

			if ( $id > 0 ) {
				$log_items = $this->get_table_manager()->fetch_log_items_by_id( array( $id ) );
				if ( count( $log_items ) > 0 ) {
					$log_item = $log_items[0];

					ob_start();
					?>
					<table style="width: 100%;">
						<tr style="background: #eee;">
							<td style="padding: 5px;"><?php _e( 'Sent at', 'email-log' ); ?>:</td>
							<td style="padding: 5px;"><?php echo $log_item['sent_date'] ?></td>
						</tr>
						<tr style="background: #eee;">
							<td style="padding: 5px;"><?php _e( 'To', 'email-log' ); ?>:</td>
							<td style="padding: 5px;"><?php echo $log_item['to_email'] ?></td>
						</tr>
						<tr style="background: #eee;">
							<td style="padding: 5px;"><?php _e( 'Subject', 'email-log' ); ?>:</td>
							<td style="padding: 5px;"><?php echo $log_item['subject'] ?></td>
						</tr>

						<?php
					   /**
						* After the headers are displayed in the View Message thickbox.
					    * This action can be used to add additional headers.
						*
						* @since 2.0.0
						*
						* @param array $log_item Log item that is getting rendered.
						*/
						do_action( 'el_view_log_after_headers', $log_item );
						?>

					</table>

					<div id="tabs">
						<ul>
							<li><a href="#tabs-1"><?php _e( 'HTML', 'email-log' ); ?></a></li>
							<li><a href="#tabs-2"><?php _e( 'Text', 'email-log' ); ?></a></li>
						</ul>
						<div id="tabs-1">
							<?php echo $log_item['message']; ?>
						</div>
						<div id="tabs-2">
							<textarea class="tabs-text-textarea"><?php echo esc_textarea( $log_item['message'] ); ?></textarea>
						</div>
					</div>

					<div id="view-message-footer">
						<a href="#" id="thickbox-footer-close"><?php _e( 'Close', 'email-log' ); ?></a>
					</div>

					<?php
					$output = ob_get_clean();
					echo $output;
				}
			}
		}

		die(); // this is required to return a proper result.
	}

	/**
	 * Delete log entries by id.
	 *
	 * @param array $data Request data.
	 */
	public function delete_logs( $data ) {
		$ids = $data['email-log'];

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		$ids = array_map( 'absint', $ids );
		$id_list = implode( ',', $ids );

		$logs_deleted = $this->get_table_manager()->delete_logs( $id_list );
		$this->render_log_deleted_notice( $logs_deleted );
	}

	/**
	 * Delete all log entries.
	 */
	public function delete_all_logs() {
		$logs_deleted = $this->get_table_manager()->delete_all_logs();
		$this->render_log_deleted_notice( $logs_deleted );
	}

	/**
	 * Render Logs deleted notice.
	 *
	 * @param int|False $logs_deleted Number of entries deleted, False otherwise.
	 */
	protected function render_log_deleted_notice( $logs_deleted ) {
		$message = __( 'There was some problem in deleting the email logs', 'email-log' );
		$type    = 'error';

		if ( absint( $logs_deleted ) > 0 ) {
			$message = sprintf( _n( '1 email log deleted.', '%s email logs deleted', $logs_deleted, 'email-log' ), $logs_deleted );
			$type    = 'updated';
		}

		add_settings_error(
			'log-list',
			'deleted-email-logs',
			$message,
			$type
		);
	}

	/**
	 * Get TableManager instance.
	 *
	 * @return \EmailLog\Core\DB\TableManager TableManager instance.
	 */
	protected function get_table_manager() {
		$email_log = email_log();

		return $email_log->table_manager;
	}
}
