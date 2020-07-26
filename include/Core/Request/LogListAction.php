<?php namespace EmailLog\Core\Request;

use EmailLog\Core\Loadie;
use EmailLog\Core\UI\Page\LogListPage;
use EmailLog\Util;

/**
 * Actions performed in Log List.
 *
 * @since 2.0.0
 */
class LogListAction implements Loadie {

	/**
	 * Setup actions.
	 *
	 * @since 2.4.0 Added Star Email Log AJAX.
	 * @since 2.4.0 Display Plain type email using <pre>.
	 *
	 * @inheritdoc
	 */
	public function load() {
		add_action( 'wp_ajax_el-log-list-view-message', array( $this, 'view_log_message' ) );
		add_action( 'wp_ajax_' . LogListPage::STAR_EMAIL_ACTION, array( $this, 'star_email' ) );

		add_action( 'el-log-list-delete', array( $this, 'delete_logs' ) );
		add_action( 'el-log-list-delete-all', array( $this, 'delete_all_logs' ) );

		add_action( 'el-log-list-manage-user-roles-changed', array( $this, 'update_capabilities_for_user_roles' ), 10, 2 );
	}

	/**
	 * AJAX callback for displaying email content.
	 *
	 * @since 2.4.0 Show Active Tab based on the Email's content type.
	 * @since 1.6
	 */
	public function view_log_message() {
		$this->fail_if_user_cant_perform_email_log_action();

		$id = absint( $_GET['log_id'] );

		if ( $id <= 0 ) {
			wp_die();
		}

		$log_items = $this->get_table_manager()->fetch_log_items_by_id( array( $id ) );
		if ( count( $log_items ) > 0 ) {
			$log_item = $log_items[0];

			$headers = array();
			if ( ! empty( $log_item['headers'] ) ) {
				$parser  = new \EmailLog\Util\EmailHeaderParser();
				$headers = $parser->parse_headers( $log_item['headers'] );
			}

			$active_tab = '0';
			if ( isset( $headers['content_type'] ) && 'text/html' === $headers['content_type'] ) {
				$active_tab = '1';
			}

			ob_start();
			?>
			<table style="width: 100%;">
				<tr style="background: #eee;">
					<td style="padding: 5px;"><?php _e( 'Sent at', 'email-log' ); ?>:</td>
					<td style="padding: 5px;"><?php echo esc_html( $log_item['sent_date'] ); ?></td>
				</tr>
				<tr style="background: #eee;">
					<td style="padding: 5px;"><?php _e( 'To', 'email-log' ); ?>:</td>
					<td style="padding: 5px;"><?php echo esc_html( $log_item['to_email'] ); ?></td>
				</tr>
				<tr style="background: #eee;">
					<td style="padding: 5px;"><?php _e( 'Subject', 'email-log' ); ?>:</td>
					<td style="padding: 5px;"><?php echo esc_html( $log_item['subject'] ); ?></td>
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
				<ul data-active-tab="<?php echo absint( $active_tab ); ?>">
					<li><a href="#tabs-text"><?php _e( 'Raw Email Content', 'email-log' ); ?></a></li>
					<li><a href="#tabs-preview"><?php _e( 'Preview Content as HTML', 'email-log' ); ?></a></li>
				</ul>

				<div id="tabs-text">
					<pre class="tabs-text-pre"><?php echo esc_textarea( $log_item['message'] ); ?></pre>
				</div>

				<div id="tabs-preview">
					<?php echo wp_kses( $log_item['message'], $this->el_kses_allowed_html( 'post' ) ); ?>
				</div>
			</div>

			<div id="view-message-footer">
				<a href="#" class="button action" id="thickbox-footer-close"><?php _e( 'Close', 'email-log' ); ?></a>
			</div>

			<?php
			echo ob_get_clean();
		}

		wp_die(); // this is required to return a proper result.
	}

	/**
	 * Stars the selected Email Log.
	 *
	 * @since 2.5.0
	 */
	public function star_email() {
		$this->fail_if_user_cant_perform_email_log_action();

		check_ajax_referer( LogListPage::STAR_EMAIL_ACTION );

		$is_star         = sanitize_text_field( Util\el_array_get( $_POST, 'is_star', '0' ) ) === '1';
		$log_id          = absint( Util\el_array_get( $_POST, 'log_id', 0 ) );
		$current_user_id = get_current_user_id();

		if ( 0 === $log_id ) {
			wp_send_json_error( new \WP_Error( 'INVALID_LOG_ID', 'Invalid Log ID' ) );
		}

		$starred_log_ids = get_user_meta( $current_user_id, LogListPage::STARRED_LOGS_META_KEY, true );

		if ( empty( $starred_log_ids ) ) {
			$starred_log_ids = array();
		}

		if ( $is_star ) {
			$starred_log_ids = array_merge( $starred_log_ids, array( $log_id ) );
		} else {
			$key = array_search( $log_id, $starred_log_ids, true );
			unset( $starred_log_ids[ $key ] );
		}

		$update = update_user_meta(
			$current_user_id,
			LogListPage::STARRED_LOGS_META_KEY,
			$starred_log_ids
		);

		if ( ! $update ) {
			wp_send_json_error();
		}

		wp_send_json_success();
	}

	/**
	 * Delete log entries by id.
	 *
	 * @param array $data Request data.
	 */
	public function delete_logs( $data ) {
		$this->fail_if_user_cant_perform_email_log_action();

		if ( ! is_array( $data ) || ! array_key_exists( 'email-log', $data ) ) {
			return;
		}

		$ids = $data['email-log'];

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		$ids     = array_map( 'absint', $ids );
		$id_list = implode( ',', $ids );

		$logs_deleted = $this->get_table_manager()->delete_logs( $id_list );
		$this->render_log_deleted_notice( $logs_deleted );
	}

	/**
	 * Delete all log entries.
	 */
	public function delete_all_logs() {
		$this->fail_if_user_cant_perform_email_log_action();

		$logs_deleted = $this->get_table_manager()->delete_all_logs();
		$this->render_log_deleted_notice( $logs_deleted );
	}

	/**
	 * Update user role capabilities when the allowed user role list is changed.
	 *
	 * The capability will be removed from old user roles and added to new user roles.
	 *
	 * @since 2.1.0
	 *
	 * @param array $old_roles Old user roles.
	 * @param array $new_roles New user roles.
	 */
	public function update_capabilities_for_user_roles( $old_roles, $new_roles ) {
		foreach ( $old_roles as $old_role ) {
			$role = get_role( $old_role );

			if ( ! is_null( $role ) ) {
				$role->remove_cap( LogListPage::CAPABILITY );
			}
		}

		foreach ( $new_roles as $new_role ) {
			$role = get_role( $new_role );

			if ( ! is_null( $role ) ) {
				$role->add_cap( LogListPage::CAPABILITY );
			}
		}
	}

	/**
	 * Render Logs deleted notice.
	 *
	 * @param false|int $logs_deleted Number of entries deleted, False otherwise.
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

	/**
	 * Allows `<link>` tag in wp_kses().
	 *
	 * Gets the list of allowed HTML for the `post` context.
	 * Appends <link> tag to the above list and returns the array.
	 *
	 * @since 2.3.0
	 *
	 * @param string $context Optional. Default `post`. The context for which to retrieve tags.
	 *
	 * @return array List of allowed tags and their allowed attributes.
	 */
	protected function el_kses_allowed_html( $context = 'post' ) {
		$allowed_tags = wp_kses_allowed_html( $context );

		$allowed_tags['link'] = array(
			'rel'   => true,
			'href'  => true,
			'type'  => true,
			'media' => true,
		);

		return $allowed_tags;
	}

	/**
	 * Fail if the currently logged in user can't perform email log action.
	 */
	protected function fail_if_user_cant_perform_email_log_action() {
		if ( ! current_user_can( LogListPage::CAPABILITY ) ) {
			wp_die();
		}
	}
}
