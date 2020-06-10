<?php namespace EmailLog\Core\UI\Setting;

use  \EmailLog\Core\UI\Page\SettingsPage;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * All Email Log Core settings.
 *
 * @since 2.1.0
 */
class CoreSetting extends Setting {

	protected function initialize() {
		$this->section->id          = 'email-log-core';
		$this->section->title       = __( 'Core Email Log Settings', 'email-log' );
		$this->section->option_name = 'email-log-core';

		$this->section->field_labels = array(
			'allowed_user_roles'    => __( 'Allowed User Roles', 'email-log' ),
			'remove_on_uninstall'   => __( 'Remove Data on Uninstall?', 'email-log' ),
			'hide_dashboard_widget' => __( 'Disable Dashboard Widget', 'email-log' ),
			'db_size_notification'  => __( 'Database Size Notification', 'email-log' ),
		);

		$this->section->default_value = array(
			'allowed_user_roles'    => array(),
			'remove_on_uninstall'   => '',
			'hide_dashboard_widget' => false,
			'db_size_notification'  => array(
				'notify'                    => false,
				'admin_email'               => '',
				'logs_threshold'            => '',
				'log_threshold_met'         => false,
				'threshold_email_last_sent' => false,
			),
		);

		$this->load();
	}

	/**
	 * Override `load` method so that the core settings are displayed first.
	 *
	 * @inheritdoc
	 */
	public function load() {
		add_filter( 'el_setting_sections', array( $this, 'register' ), 9 );

		add_action( 'add_option_' . $this->section->option_name, array( $this, 'allowed_user_roles_added' ), 10, 2 );
		add_action( 'update_option_' . $this->section->option_name, array( $this, 'allowed_user_roles_changed' ), 10, 2 );

		add_action( 'el_email_log_inserted', array( $this, 'verify_email_log_threshold' ) );
		add_action( 'el_trigger_notify_email_when_log_threshold_met', array( $this, 'trigger_threshold_met_notification_email' ) );
	}

	/**
	 * Renders the Email Log `Allowed User Roles` settings.
	 *
	 * @param array $args Arguments.
	 */
	public function render_allowed_user_roles_settings( $args ) {
		$option         = $this->get_value();
		$selected_roles = $option[ $args['id'] ];

		$field_name = $this->section->option_name . '[' . $args['id'] . '][]';

		$available_roles = get_editable_roles();
		unset( $available_roles['administrator'] );
		?>

		<p>
			<input type="checkbox" checked disabled><?php _e( 'Administrator', 'email-log' ); ?>
		</p>

		<?php foreach ( $available_roles as $role_id => $role ) : ?>
			<p>
				<input type="checkbox" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $role_id ); ?>"
					<?php \EmailLog\Util\checked_array( $selected_roles, $role_id ); ?>>

				<?php echo $role['name']; ?>
			</p>
		<?php endforeach; ?>

		<p>
			<em>
				<?php _e( '<strong>Note:</strong> Users with the above User Roles can view Email Logs.', 'email-log' ); ?>
				<?php _e( 'Administrator role always has access and cannot be disabled.', 'email-log' ); ?>
			</em>
		</p>

		<?php
	}

	/**
	 * Sanitize allowed user roles setting.
	 *
	 * @param array $roles User selected user roles.
	 *
	 * @return array Sanitized user roles.
	 */
	public function sanitize_allowed_user_roles( $roles ) {
		if ( ! is_array( $roles ) ) {
			return array();
		}

		return array_map( 'sanitize_text_field', $roles );
	}

	/**
	 * Renders the Email Log `Remove Data on Uninstall?` settings.
	 *
	 * @param array $args
	 */
	public function render_remove_on_uninstall_settings( $args ) {
		$option      = $this->get_value();
		$remove_data = $option[ $args['id'] ];

		$field_name = $this->section->option_name . '[' . $args['id'] . ']';
		?>

		<input type="checkbox" name="<?php echo esc_attr( $field_name ); ?>" value="true" <?php checked( 'true', $remove_data ); ?>>
		<?php _e( 'Check this box if you would like to completely remove all of its data when the plugin is deleted.', 'email-log' ) ?>

		<p>
			<em>
				<?php printf(
					__( '<strong>Note:</strong> You can also export the Email Logs using our <a href="%s" rel="noopener noreferrer" target="_blank">Export Logs</a> add-on.', 'email-log' ),
					'https://wpemaillog.com/addons/export-logs/?utm_campaign=Upsell&utm_medium=wpadmin&utm_source=settings&utm_content=el'
				); ?>
			</em>
		</p>

		<?php
	}

	/**
	 * Sanitize Remove on uninstall value.
	 *
	 * @param string $value User entered value.
	 *
	 * @return string Sanitized value.
	 */
	public function sanitize_remove_on_uninstall( $value ) {
		return sanitize_text_field( $value );
	}

	/**
	 * Allowed user role list option is added.
	 *
	 * @param string $option Option name.
	 * @param array  $value  Option value.
	 */
	public function allowed_user_roles_added( $option, $value ) {
		$this->allowed_user_roles_changed( array(), $value );
	}

	/**
	 * Allowed user role list option was update.
	 *
	 * Change user role capabilities when the allowed user role list is changed.
	 *
	 * @param array $old_value Old Value.
	 * @param array $new_value New Value.
	 */
	public function allowed_user_roles_changed( $old_value, $new_value ) {
		$old_roles = $this->get_user_roles( $old_value );
		$new_roles = $this->get_user_roles( $new_value );

		/**
		 * The user roles who can manage email log list is changed.
		 *
		 * @since 2.1.0
		 *
		 * @param array $old_roles Old user roles.
		 * @param array $new_roles New user roles.
		 */
		do_action( 'el-log-list-manage-user-roles-changed', $old_roles, $new_roles );
	}

	/**
	 * Get User roles from option value.
	 *
	 * @access protected
	 *
	 * @param array $option Option value
	 *
	 * @return array User roles.
	 */
	protected function get_user_roles( $option ) {
		if ( ! array_key_exists( 'allowed_user_roles', $option ) ) {
			return array();
		}

		$user_roles = $option['allowed_user_roles'];
		if ( ! is_array( $user_roles ) ) {
			$user_roles = array( $user_roles );
		}

		return $user_roles;
	}

	/**
	 * Renders the Email Log `Remove Data on Uninstall?` settings.
	 *
	 * @param array $args
	 */
	public function render_hide_dashboard_widget_settings( $args ) {
		$option                = $this->get_value();
		$hide_dashboard_widget = $option[ $args['id'] ];

		$field_name = $this->section->option_name . '[' . $args['id'] . ']';
		?>

		<input type="checkbox" name="<?php echo esc_attr( $field_name ); ?>" value="true" <?php checked( 'true', $hide_dashboard_widget ); ?>>
		<?php _e( 'Check this box if you would like to disable dashboard widget.', 'email-log' ) ?>

		<p>
			<em>
				<?php printf(
					__( '<strong>Note:</strong> Each users can also disable dashboard widget using screen options', 'email-log' )
				); ?>
			</em>
		</p>

		<?php
	}

	/**
	 * Renders the Email Log `Database Size Notification` settings.
	 *
	 * @since 2.3.0
	 *
	 * @param array $args
	 */
	public function render_db_size_notification_settings( $args ) {
		$option                    = $this->get_value();
		$db_size_notification_data = $option[ $args['id'] ];

		$field_name = $this->section->option_name . '[' . $args['id'] . ']';
		// Since we store three different fields, give each field a unique name.
		$db_size_notification_field_name = $field_name . '[notify]';
		$admin_email_field_name          = $field_name . '[admin_email]';
		$logs_threshold_field_name       = $field_name . '[logs_threshold]';

		$email_log  = email_log();
		$logs_count = $email_log->table_manager->get_logs_count();

		$admin_email_input_field = sprintf(
			'<input type="email" name="%1$s" value="%2$s" size="35" />', esc_attr( $admin_email_field_name ), empty( $db_size_notification_data['admin_email'] ) ? get_option( 'admin_email', '' ) : esc_attr( $db_size_notification_data['admin_email'] ) );

		$logs_threshold_input_field = sprintf( '<input type="number" name="%1$s" placeholder="5000" value="%2$s" min="0" max="99999999" />',
			esc_attr( $logs_threshold_field_name ),
			empty( $db_size_notification_data['logs_threshold'] ) ? '' : esc_attr( $db_size_notification_data['logs_threshold'] )
		);
		?>

        <input type="checkbox" name="<?php echo esc_attr( $db_size_notification_field_name ); ?>" value="true" <?php
		checked( true, $db_size_notification_data['notify'] ); ?> />
		<?php
		// The values within each field are already escaped.
		printf( __( 'Notify %1$s if there are more than %2$s logs.', 'email-log' ),
			$admin_email_input_field,
			$logs_threshold_input_field
		);
		?>
        <p>
            <em>
				<?php printf(
					__( '%1$s There are %2$s email logs currently logged in the database.', 'email-log' ),
					'<strong>Note:</strong>',
					'<strong>' . esc_attr( $logs_count ) . '</strong>'
				); ?>
            </em>
        </p>
		<?php if ( ! empty( $db_size_notification_data['threshold_email_last_sent'] ) ) : ?>
            <p>
				<?php printf(
					__( 'Last notification email was sent on %1$s. Click %2$s button to reset sending the notification.', 'email-log' ),
					'<strong>' . get_date_from_gmt( date( 'Y-m-d H:i:s', $db_size_notification_data['threshold_email_last_sent'] ), \EmailLog\Util\get_user_defined_date_time_format() ) . '</strong>',
					'<b>Save</b>'
				); ?>
            </p>
		<?php
		endif;
		/**
		 * After DB size notification setting in Settings page.
		 *
		 * @since 2.4.0
		 */
		do_action( 'el_after_db_size_notification_setting' );
	}

	/**
	 * Removes any additional keys set other than the ones in db_size_notification array.
	 *
	 * @since 2.3.0
	 *
	 * @param array $arr `db_size_notification` option array.
	 *
	 * @return array `db_size_notification` option array with keys removed other than the allowed.
	 */
	protected function restrict_array_to_db_size_notification_setting_keys( $arr ) {
		$allowed_keys = array_keys( $this->section->default_value['db_size_notification'] );
		$arr_keys     = array_keys( $arr );

		// Return the array when only the allowed keys exist.
		$intersecting_keys = array_intersect( $allowed_keys, $arr_keys );
		if ( count( $allowed_keys ) === count( $intersecting_keys ) ) {
			return $arr;
		}

		// Otherwise remove keys that aren't expected.
		$diff_keys = array_diff_key( $arr, $allowed_keys );
		foreach ( $diff_keys as $key ) {
			unset( $arr[ $key ] );
		}

		return $arr;
	}

	/**
	 * Sanitizes the db_size_notification option.
	 *
	 * @since 2.3.0
	 *
	 * @param array $db_size_notification_data
	 *
	 * @return array $db_size_notification_data
	 */
	public function sanitize_db_size_notification( $db_size_notification_data ) {
		$db_size_notification_data = $this->restrict_array_to_db_size_notification_setting_keys( $db_size_notification_data );

		foreach ( $db_size_notification_data as $setting => $value ) {
			if ( 'notify' === $setting ) {
				$db_size_notification_data[ $setting ] = \filter_var( $value, FILTER_VALIDATE_BOOLEAN );
			} elseif ( 'admin_email' === $setting ) {
				$db_size_notification_data[ $setting ] = \sanitize_email( $value );
			} elseif ( 'logs_threshold' === $setting ) {
				$db_size_notification_data[ $setting ] = absint( \sanitize_text_field( $value ) );
			}
		}

		// wp_parse_args won't merge nested array keys. So add the key here if it is not set.
		if ( ! array_key_exists( 'notify', $db_size_notification_data ) ) {
			$db_size_notification_data['notify'] = false;
		}
		if ( ! array_key_exists( 'log_threshold_met', $db_size_notification_data ) ) {
			$db_size_notification_data['log_threshold_met'] = false;
		}
		if ( ! array_key_exists( 'threshold_email_last_sent', $db_size_notification_data ) ) {
			$db_size_notification_data['threshold_email_last_sent'] = false;
		}

		return $db_size_notification_data;
	}

	/**
	 * Triggers the Cron to notify admin via email.
	 *
	 * Email is triggered only when the threshold setting is met.
	 *
	 * @since 2.3.0
	 */
	public function verify_email_log_threshold() {
		$cron_hook = 'el_trigger_notify_email_when_log_threshold_met';
		if ( ! wp_next_scheduled( $cron_hook ) ) {
			wp_schedule_event( time(), 'hourly', $cron_hook );
		}
	}

	/**
	 * Utility method to verify all the given keys exist in the given array.
	 *
	 * This method helps reduce the Cyclomatic Complexity in its parent method.
	 *
	 * @since 2.3.0
	 *
	 * @param array $arr  The array whose keys must be checked.
	 * @param array $keys All the required keys that the array must contain.
	 *
	 * @return bool
	 */
	protected function has_array_contains_required_keys( $arr, $keys ) {
		$has_keys = true;

		if ( ! is_array( $arr ) || ! is_array( $keys ) ) {
			return false;
		}

		foreach ( $arr as $key => $value ) {
			$has_keys = $has_keys && in_array( $key, $keys, true );
		}

		return $has_keys;
	}

	/**
	 * Send the Threshold notification email to the admin.
	 *
	 * @since 2.3.0
	 */
	public function trigger_threshold_met_notification_email() {
		$email_log  = email_log();
		$logs_count = absint( $email_log->table_manager->get_logs_count() );

		$setting_data = $this->get_value();

		// Return early.
		if ( ! array_key_exists( 'db_size_notification', $setting_data ) ) {
			return;
		}

		$db_size_notification_key  = 'db_size_notification';
		$db_size_notification_data = $setting_data[ $db_size_notification_key ];

		// Return early.
		$keys = array_keys( $this->section->default_value['db_size_notification'] );
		if ( ! $this->has_array_contains_required_keys( $db_size_notification_data, $keys ) ) {
			return;
		}

		$is_notification_enabled = $db_size_notification_data['notify'];
		$admin_email             = $db_size_notification_data['admin_email'];
		$logs_threshold          = absint( $db_size_notification_data['logs_threshold'] );
		$logs_threshold_met      = $db_size_notification_data['log_threshold_met'];

		// Ideally threshold cannot be 0. Also, skip sending email if it is already sent.
		if ( 0 === $logs_threshold || true === $logs_threshold_met ) {
			return;
		}

		if ( $logs_count < $logs_threshold ) {
			return;
		}

		$this->register_threshold_met_admin_notice();

		if ( $is_notification_enabled && is_email( $admin_email ) ) {
			$subject = sprintf( __( 'Email Log Plugin: Your log threshold of %s has been met', 'email-log' ), $logs_threshold );
			$message = <<<EOT
<p>This email is generated by the Email Log plugin.</p>
<p>Your log threshold of $logs_threshold has been met. You may manually delete the logs to keep your database table in size.</p>
<p>Also, consider using our <a href="https://wpemaillog.com/addons/auto-delete-logs/">Auto Delete Logs</a> plugin to delete the logs automatically.</p>
EOT;
			$headers = array( 'Content-Type: text/html; charset=UTF-8' );

			/**
			 * Filters Log threshold notification email subject.
			 *
			 * @since 2.3.0
			 *
			 * @param string $subject The email subject.
			 */
			$subject = apply_filters( 'el_log_threshold_met_notification_email_subject', $subject );

			/**
			 * Filters Log threshold notification email body.
			 *
			 * @since 2.3.0
			 *
			 * @param string $message        The email body.
			 * @param int    $logs_threshold The log threshold value set by the user.
			 */
			$message = apply_filters( 'el_log_threshold_met_notification_email_body', $message, $logs_threshold );

			wp_mail( $admin_email, $subject, $message, $headers );

			$setting_data[ $db_size_notification_key ]['log_threshold_met']         = true;
			$setting_data[ $db_size_notification_key ]['threshold_email_last_sent'] = time();
			\update_option( $this->section->option_name, $setting_data );
		}
	}

	/**
	 * Registers the Email Log threshold met admin notice.
	 *
	 * @since 2.3.0
	 */
	public function register_threshold_met_admin_notice() {
		add_action( 'admin_notices', array( $this, 'render_log_threshold_met_notice' ) );
	}

	/**
	 * Displays the Email Log threshold met admin notice.
	 *
	 * @since 2.3.0
	 */
	public function render_log_threshold_met_notice() {
		$email_log      = email_log();
		$logs_count     = absint( $email_log->table_manager->get_logs_count() );
		$notice_message = sprintf( __( 'Currently there are %1$s logged, which is more than the threshold that is set in the %2$s screen. You can delete some logs or increase the threshold. You can also use our %3$s add-on to automatically delete logs', 'email-log' ),
			$logs_count . _n( ' email log', ' email logs', $logs_count, 'email-log' ),
			'<a href="' . esc_url( admin_url( 'admin.php?page=' . SettingsPage::PAGE_SLUG ) ) . '">settings</a> screen',
			'<a href="' . esc_url( 'https://wpemaillog.com/addons/auto-delete-logs/' ) . '">Auto Delete Logs</a>'
			 );
		?>
        <div class="notice notice-warning is-dismissible">
            <p><?php echo $notice_message; ?></p>
        </div>
		<?php
	}

}
