<?php namespace EmailLog\Util;

/**
 * Email Log Helper functions.
 * Some of these functions would be used the addons.
 */

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
 * @return string        Sanitized email.
 */
function sanitize_email_with_name( $string ) {
	$string = trim( $string );

	$bracket_pos = strpos( $string, '<' );
	if ( false !== $bracket_pos ) {
		// Text before the bracketed email is the name.
		if ( $bracket_pos > 0 ) {
			$name = substr( $string, 0, $bracket_pos );
			$name = str_replace( '"', '', $name );
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
 * @return array List of Columns to export.
 */
function get_log_columns_to_export() {

	if ( is_plugin_active( 'email-log-more-fields/email-log-more-fields.php' ) ) {
		return array( 'id', 'sent_date', 'to_email', 'subject', 'from', 'cc', 'bcc', 'reply-to', 'attachment' );
	}

	return array( 'id', 'sent_date', 'to_email', 'subject' );
}

/**
 * Returns TRUE if the User is Administrator or the User's role is allowed in Plugin's settings page.
 *
 * @since 2.1.0
 *
 * @return bool
 */
function can_current_user_view_email_log() {
	$return_value = false;
	$option       = get_option( 'el_email_log_core' );

	if ( current_user_can( 'administrator' ) ) {
		$return_value = true;
	} elseif ( ! is_admin() && ! current_user_can( 'administrator' ) ) {
		if ( $option && is_array( $option ) && array_key_exists( 'allowed_user_roles', $option ) ) {
			$user               = wp_get_current_user();
			$allowed_user_roles = $option['allowed_user_roles'];
			$allowed_user_roles = array_map( 'strtolower', $allowed_user_roles );
			$matched_role       = array_intersect( (array) $user->roles, $allowed_user_roles );
			if ( is_array( $matched_role ) && ! empty( $matched_role ) ) {
				$return_value = true;
			}
		}
	}

	return $return_value;
}

/**
 * Checks the Checkbox when values are present in a given array.
 *
 * Use this function in Checkbox fields.
 *
 * @since 2.1.0
 *
 * @param array $values   List of all possible values.
 * @param string $current The current value to be checked.
 */
function checked_array( $values, $current ) {
	if ( in_array( $current, $values ) ) {
		echo "checked='checked'";
	}
}