<?php namespace EmailLog\Util;

/**
 * Email Log Helper functions.
 * Some of these functions would be used the addons.
 */
defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Perform additional sanitation of emails.
 *
 * @since 1.9
 *
 * @param string $email    Email string to be sanitized.
 * @param bool   $multiple (Optional) Should multiple emails be allowed. True by default.
 *
 * @return string Sanitized email.
 */
function sanitize_email( $email, $multiple = true ) {
	$emails = explode( ',', $email );
	if ( ! $multiple ) {
		$emails = array_slice( $emails, 0, 1 );
	}

	$cleaned_emails = array_map( __NAMESPACE__ . '\\sanitize_email_with_name', $emails );

	return implode( ', ', $cleaned_emails );
}

/**
 * Sanitize email with name.
 *
 * @since 1.9
 *
 * @param string $string Email string to be sanitized.
 *
 * @return string Sanitized email.
 */
function sanitize_email_with_name( $string ) {
	$string = trim( $string );

	$bracket_pos = strpos( $string, '<' );
	if ( false !== $bracket_pos ) {
		if ( $bracket_pos > 0 ) {
			$name = substr( $string, 0, $bracket_pos );
			$name = trim( $name );

			$email = substr( $string, $bracket_pos + 1 );
			$email = str_replace( '>', '', $email );

			return sanitize_text_field( $name ) . ' <' . \sanitize_email( $email ) . '>';
		}
	}

	return \sanitize_email( $string );
}

/**
 * Gets the columns to export logs.
 *
 * If the More Fields add-on is active, additional columns are returned.
 *
 * @since 2.0.0
 *
 * @return string[] List of Columns to export.
 */
function get_log_columns_to_export() {

	if ( is_plugin_active( 'email-log-more-fields/email-log-more-fields.php' ) ) {
		return array(
			'id',
			'sent_date',
			'to_email',
			'subject',
			'from',
			'cc',
			'bcc',
			'reply-to',
			'attachment',
		);
	}

	return array( 'id', 'sent_date', 'to_email', 'subject' );
}

/**
 * Is it an admin request and not an ajax request.
 *
 * @since 2.1
 *
 * @return bool True if admin non ajax request, False otherwise.
 */
function is_admin_non_ajax_request() {
	if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
		return false;
	}

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
		return false;
	}

	return is_admin();
}

/**
 * Checks the Checkbox when values are present in a given array.
 *
 * Use this function in Checkbox fields.
 *
 * @since 2.1.0
 *
 * @param array  $values  List of all possible values.
 * @param string $current The current value to be checked.
 */
function checked_array( $values, $current ) {
	if ( ! is_array( $values ) ) {
		return;
	}

	if ( in_array( $current, $values, true ) ) {
		echo "checked='checked'";
	}
}

/**
 * Return failure icon.
 *
 * @since 2.3.2
 *
 * @return string Failure icon markup.
 */
function get_failure_icon() {
	return <<<EOT
<span class="dashicons dashicons-dismiss"></span>
EOT;
}

/**
 * Return success icon.
 *
 * @since 2.3.2
 *
 * @return string Success icon markup.
 */
function get_success_icon() {
	return <<<EOT
<span class="dashicons dashicons-yes-alt"></span>
EOT;

}

/**
 * Stringify arrays.
 *
 * If the parameter is an array, then return delimiter separated values of the array.
 * Otherwise return the parameter.
 *
 * @since 2.3.0
 * @since 2.3.2 Renamed name to `Stringify`.
 *
 * @param array|string $may_be_array The array whose values are to be converted to string.
 * @param string       $delimiter    Optional. Default is `,`.
 *
 * @return string Stringified value.
 */
function stringify( $may_be_array, $delimiter = ',' ) {
	if ( ! is_array( $may_be_array ) ) {
		return (string) $may_be_array;
	}

	return implode( $delimiter, $may_be_array );
}

/**
 * Gets the User defined Date time format.
 *
 * @used-by \EmailLog\Core\UI\Setting\CoreSetting
 *
 * @since   2.3.0
 *
 * @return string
 */
function get_user_defined_date_time_format() {
	return sprintf( '%1$s %2$s', get_option( 'date_format', 'Y-m-d' ), get_option( 'time_format', 'g:i a' ) );
}

/**
 * Get the display format for displaying the email log time.
 *
 * @since 2.4.3
 *
 * @return string Email log time display format.
 */
function get_display_format_for_log_time() {
	$default_time_format = get_option( 'time_format', 'g:i:s a' );

	if ( false === stripos( $default_time_format, 's' ) ) {
		/* translators: Email Log time display format, see http://php.net/date */
		$default_time_format = __( 'g:i:s a', 'email-log' );
	}

	/**
	 * Filter the time format string for displaying log time.
	 *
	 * @since 2.4.3
	 *
	 * @param string $default_time_format Default time format.
	 */
	return apply_filters( 'el_log_time_display_format', $default_time_format );
}

/**
 * Gets the value by key from the array.
 *
 * If the key isn't found, then null is returned.
 *
 * @since 2.3.0
 *
 * @param array  $array   The actual array.
 * @param string $key     The key whose value is to be retrieved.
 * @param string $default Optional.
 *
 * @return mixed|null
 */
function el_array_get( $array, $key, $default = null ) {
	return isset( $array[ $key ] ) ? $array[ $key ] : $default;
}

/**
 * Returns TRUE if the given search term is Advanced Search Term.
 *
 * @param string $term Search Term.
 *
 * @return bool
 */
function is_advanced_search_term( $term ) {
	if ( ! is_string( $term ) ) {
		return false;
	}

	$predicates = get_advanced_search_term_predicates( $term );

	return ! empty( $predicates );
}

/**
 * Gets the Search Term Predicates.
 *
 * Example:
 *
 * If $term = to:admin@local.wordpress.test then,
 *
 * the output would be
 *
 * $output = array(
 *      'to' => admin@local.wordpress.test
 * )
 *
 * @since 2.3.0
 *
 * @param string $term Search Term.
 *
 * @return array
 */
function get_advanced_search_term_predicates( $term ) {
	if ( ! is_string( $term ) ) {
		return array();
	}

	$predicates           = explode( ' ', $term );
	$predicates_organized = array();

	foreach ( $predicates as $predicate ) {
		$is_match = preg_match( '/(id|email|to|cc|bcc|reply-to):(.*)$/', $predicate, $matches );
		if ( 1 === $is_match ) {
			$predicates_organized[ $matches[1] ] = $matches[2];
		}
	}

	return $predicates_organized;
}

/**
 * Gets the Advanced Search URL.
 *
 * @since 2.3.0
 *
 * @return string
 */
function get_advanced_search_url() {
	$admin_url = get_admin_url( null, 'admin.php?page=email-log' );

	return add_query_arg( 'el_as', 1, $admin_url );
}

/**
 * Gets the Column labels to be used in LogList table.
 *
 * Deprecated. This is currently used by Email Log - Export Logs add-on v1.2.1 and will eventually be removed.
 *
 * @since 2.3.0
 * @since 2.3.2 Deprecated.
 *
 * @param string $db_column Column ID.
 *
 * @return string Column label.
 */
function get_column_label_by_db_column( $db_column ) {
	return get_column_label( $db_column );
}

/**
 * Get Column label based on column name.
 *
 * @since 2.3.2
 *
 * @param string $column_name Column name.
 *
 * @return string Column label.
 */
function get_column_label( $column_name ) {
	$labels = get_column_label_map();

	if ( ! array_key_exists( $column_name, $labels ) ) {
		return $column_name;
	}

	return $labels[ $column_name ];
}

/**
 * Returns an array of Email Log columns.
 *
 * Keys are the column names in the DB.
 * This holds true except for CC, BCC & Reply To as they are put under one column `headers`.
 *
 * @since 2.3.2
 *
 * @return array Key value pair of Email Log columns.
 */
function get_column_label_map() {
	$labels = array(
		'id'          => __( 'ID', 'email-log' ),
		'to_email'    => __( 'To', 'email-log' ),
		'subject'     => __( 'Subject', 'email-log' ),
		'message'     => __( 'Message', 'email-log' ),
		'attachments' => __( 'Attachment', 'email-log' ),
		'sent_date'   => __( 'Sent at', 'email-log' ),
		'from'        => __( 'From', 'email-log' ),
		'cc'          => __( 'CC', 'email-log' ),
		'bcc'         => __( 'BCC', 'email-log' ),
		'reply_to'    => __( 'Reply To', 'email-log' ),
		'ip_address'  => __( 'IP Address', 'email-log' ),
		'result'      => __( 'Sent Status', 'email-log' ),
	);

	/**
	 * Filters the Labels used through out the Email Log plugin.
	 *
	 * @since 2.3.2
	 *
	 * @param array $labels List of DB Columns and its respective labels which are internationalized string.
	 *                      Example: 'id' => __( 'ID', 'email-log' ),
	 */
	return apply_filters( 'el_db_column_labels', $labels );
}
