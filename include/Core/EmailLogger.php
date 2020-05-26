<?php namespace EmailLog\Core;

/**
 * Log's emails sent through `wp_mail`.
 *
 * @package EmailLog\Core
 * @since   2.0
 */
class EmailLogger implements Loadie {

	/**
	 * Load the logger.
	 */
	public function load() {
		add_filter( 'wp_mail', array( $this, 'log_email' ) );
		add_action( 'wp_mail_failed', array( $this, 'on_email_failed' ) );

		/**
		 * These actions are required for logging BuddyPress emails, since BuddyPress does
		 * not use wp_mail for sending emails.
		 *
		 * Support for BuddyPress was added in v2.3.2
		 *
		 * @link https://github.com/sudar/email-log/issues/249
		 */
		add_action( 'bp_send_email_success', array( $this, 'log_buddy_press_email' ), 10, 2 );
		add_action( 'bp_send_email_failure', array( $this, 'log_buddy_press_email' ), 10, 2 );
	}

	/**
	 * Logs email to database.
	 *
	 * @param array $original_mail_info Information about email.
	 *
	 * @return array Information about email.
	 */
	public function log_email( $original_mail_info ) {
		/**
		 * Hook to modify wp_mail contents before Email Log plugin logs.
		 *
		 * @param array $original_mail_info {
		 *     @type string|array $to
		 *     @type string       $subject
		 *     @type string       $message
		 *     @type string|array $headers
		 *     @type string|array $attachment
		 * }
		 *
		 * @since 2.0.0
		 */
		$original_mail_info = apply_filters( 'el_wp_mail_log', $original_mail_info );

		// Sometimes the array passed to the `wp_mail` filter may not contain all the required keys.
		// See https://wordpress.org/support/topic/illegal-string-offset-attachments/.
		$mail_info = wp_parse_args(
			$original_mail_info,
			array(
				'to'          => '',
				'subject'     => '',
				'message'     => '',
				'headers'     => '',
				'attachments' => array(),
			)
		);

		$log = array(
			'to_email'        => \EmailLog\Util\stringify( $mail_info['to'] ),
			'subject'         => $mail_info['subject'],
			'message'         => $mail_info['message'],
			'headers'         => \EmailLog\Util\stringify( $mail_info['headers'], "\n" ),
			'attachment_name' => \EmailLog\Util\stringify( $mail_info['attachments'] ),
			'sent_date'       => current_time( 'mysql' ),
			'ip_address'      => $_SERVER['REMOTE_ADDR'],
			'result'          => 1,
		);

		if ( empty( $log['attachment_name'] ) ) {
			$log['attachments'] = 'false';
		} else {
			$log['attachments'] = 'true';
		}

		/**
		 * Filters the mail info right before inserting on the table.
		 *
		 * Masked fields would use this filter to avoid modifying the original data sent to
		 * `wp_mail() function`
		 *
		 * @param array $log                Email Log that is about to be inserted into db.
		 * @param array $original_mail_info Original mail info that was passed to `wp_mail` filter.
		 *
		 * @since 2.3.2
		 */
		$log = apply_filters( 'el_email_log_before_insert', $log, $original_mail_info );

		$email_log = email_log();
		$email_log->table_manager->insert_log( $log );

		/**
		 * Fires the `el_email_log_inserted` action right after the log is inserted in to DB.
		 *
		 * @since 2.3.0
		 *
		 * @param array $log {
		 *      @type string $to
		 *      @type string $subject
		 *      @type string $message
		 *      @type string $headers
		 *      @type string $attachments
		 *      @type string $attachment_name
		 *      @type string $sent_date
		 *      @type string $ip_address
		 *      @type bool   $result
		 * }
		 */
		do_action( 'el_email_log_inserted', $log );

		return $original_mail_info;
	}

	/**
	 * Updates the failed email in the DB.
	 *
	 * @param \WP_Error $wp_error The error instance.
	 *
	 * @since 2.4.0 Use is_wp_error() to validate the type of $wp_error.
	 * @since 2.3.0
	 *
	 * @see   is_wp_error()
	 * @see   email_log()
	 */
	public function on_email_failed( $wp_error ) {
		if ( ! is_wp_error( $wp_error ) ) {
			return;
		}

		// @see wp-includes/pluggable.php#500
		$mail_error_data = $wp_error->get_error_data( 'wp_mail_failed' );
		$mail_error_message = $wp_error->get_error_message( 'wp_mail_failed' );

		$this->mark_email_log_as_failed( $mail_error_data, $mail_error_message );
	}

	/**
	 * Prepare BuddyPress emails to log into database.
	 *
	 * @since 2.3.2
	 *
	 * @param bool      $status  Mail sent status.
	 * @param \BP_Email $bp_mail Information about email.
	 */
	public function log_buddy_press_email( $status, $bp_mail ) {
		if ( ! class_exists( '\\BP_Email' ) ) {
			return;
		}

		if ( $bp_mail instanceof \BP_Email ) {
			return;
		}

		$log = array(
			'to'      => array_shift( $bp_mail->get_to() )->get_address(),
			'subject' => $bp_mail->get_subject( 'replace-tokens' ),
			'message' => $bp_mail->get_content( 'replace-tokens' ),
			'headers' => $bp_mail->get_headers( 'replace-tokens ' ),
		);

		$this->log_email( $log );

		if ( ! $status ) {
			$this->mark_email_log_as_failed( $log );
		}
	}

	/**
	 * Mark email log as failed.
	 *
	 * @param array  $log           Email Log.
	 * @param string $error_message Error message.
	 *
	 * @since 2.3.2
	 * @since 2.4.0 Store the error message.
	 */
	protected function mark_email_log_as_failed( $log, $error_message = '' ) {
		if ( ! is_array( $log ) ) {
			return;
		}

		if ( ! isset( $log['to'], $log['subject'] ) ) {
			return;
		}

		$email_log = email_log();

		$log_item_id = $email_log->table_manager->fetch_log_id_by_data( $log );

		if ( empty( $log_item_id ) ) {
			return;
		}

		$email_log->table_manager->mark_log_as_failed( $log_item_id, $error_message );
	}
}
