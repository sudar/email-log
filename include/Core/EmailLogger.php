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
		add_action( 'wp_mail_failed', array( $this, 'update_email_fail_status' ) );
	}

	/**
	 * Logs email to database.
	 *
	 * @param array $mail_info Information about email.
	 *
	 * @return array Information about email.
	 */
	public function log_email( $mail_info ) {
		$email_log = email_log();
		/**
		 * Hook to modify wp_mail contents before Email Log plugin logs.
		 *
		 * @since Genesis
		 *
		 * @param array $mail_info {
		 *     @type string $to
		 *     @type string $subject
		 *     @type string $message
		 *     @type string $headers
		 *     @type string $attachment
		 * }
		 */
		$mail_info = apply_filters( 'el_wp_mail_log', $mail_info );

		// Sometimes the array passed to the `wp_mail` filter may not contain all the required keys.
		// See https://wordpress.org/support/topic/illegal-string-offset-attachments/
		$mail_info = wp_parse_args( $mail_info, array(
			'attachments' => array(),
			'to'          => '',
			'subject'     => '',
			'headers'     => '',
		) );

		$data = array(
			'attachments'     => ( count( $mail_info['attachments'] ) > 0 ) ? 'true' : 'false',
			'to_email'        => is_array( $mail_info['to'] ) ? implode( ',', $mail_info['to'] ) : $mail_info['to'],
			'subject'         => $mail_info['subject'],
			'headers'         => is_array( $mail_info['headers'] ) ? implode( "\n", $mail_info['headers'] ) : $mail_info['headers'],
			'sent_date'       => current_time( 'mysql' ),
			'attachment_name' => implode( ',', $mail_info['attachments'] ),
			// TODO: Improve the Client's IP using https://www.virendrachandak.com/techtalk/getting-real-client-ip-address-in-php-2/
			'ip_address'      => $_SERVER['REMOTE_ADDR'],
			'result'          => 1,
		);

		$message = '';

		if ( isset( $mail_info['message'] ) ) {
			$message = $mail_info['message'];
		} else {
			// wpmandrill plugin is changing "message" key to "html". See https://github.com/sudar/email-log/issues/20
			// Ideally this should be fixed in wpmandrill, but I am including this hack here till it is fixed by them.
			if ( isset( $mail_info['html'] ) ) {
				$message = $mail_info['html'];
			}
		}

		$data['message'] = $message;

		$email_log->table_manager->insert_log( $data );

		/**
		 * Fires the `el_email_log_inserted` action right after the log is inserted in to DB.
		 *
		 * @param array $data {
		 *      @type string $to
		 *      @type string $subject
		 *      @type string $message
		 *      @type string $headers
		 *      @type string $attachments
		 *      @type string $sent_date
		 * }
		 */
		do_action( 'el_email_log_inserted', $data );

		return $mail_info;
	}

	/**
	 * Updates the failed email in the DB.
	 *
	 * @since 2.3.0
	 *
	 * @param \WP_Error $wp_error The error instance.
	 */
	public function update_email_fail_status( $wp_error ) {
		if ( ! ( $wp_error instanceof \WP_Error ) ) {
			return;
		}

		$email_log       = email_log();
		$mail_error_data = $wp_error->get_error_data( 'wp_mail_failed' );

		// $mail_error_data can be of type mixed.
		if ( ! is_array( $mail_error_data ) ) {
			return;
		}

		// @see wp-includes/pluggable.php#484
		$log_item_id = $email_log->table_manager->fetch_log_item_by_item_data( $mail_error_data );
		if ( empty( $log_item_id ) ) {
			return;
		}

		$email_log->table_manager->set_log_item_fail_status_by_id( $log_item_id );
	}
}
