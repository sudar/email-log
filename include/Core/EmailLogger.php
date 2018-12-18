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
		 *     @type string|array $to
		 *     @type string $subject
		 *     @type string $message
		 *     @type string|array $headers
		 *     @type string|array $attachment
		 * }
		 */
		$mail_info = apply_filters( 'el_wp_mail_log', $mail_info );

		// Sometimes the array passed to the `wp_mail` filter may not contain all the required keys.
		// See https://wordpress.org/support/topic/illegal-string-offset-attachments/
		$mail_info_replica = wp_parse_args( $mail_info, array(
			'to'          => '',
			'subject'     => '',
			'headers'     => '',
		) );

		$data = array(
			'attachments' => ! empty( $mail_info_replica['attachments'] ) ? 'true' : 'false',
			'to_email'    => is_array( $mail_info_replica['to'] ) ? implode( ',', $mail_info_replica['to'] ) : $mail_info_replica['to'],
			'subject'     => $mail_info_replica['subject'],
			'headers'     => is_array( $mail_info_replica['headers'] ) ? implode( "\n", $mail_info_replica['headers'] ) : $mail_info_replica['headers'],
			'sent_date'   => current_time( 'mysql' ),
		);

		$message = '';

		if ( isset( $mail_info_replica['message'] ) ) {
			$message = $mail_info_replica['message'];
		} else {
			// wpmandrill plugin is changing "message" key to "html". See https://github.com/sudar/email-log/issues/20
			// Ideally this should be fixed in wpmandrill, but I am including this hack here till it is fixed by them.
			if ( isset( $mail_info_replica['html'] ) ) {
				$message = $mail_info_replica['html'];
			}
		}

		$data['message'] = $message;

		$email_log->table_manager->insert_log( $data );

		return $mail_info;
	}
}
