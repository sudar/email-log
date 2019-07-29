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

	if ( in_array( $current, $values ) ) {
		echo "checked='checked'";
	}
}

/**
 * Returns the Email failure SVG.
 *
 * @see   https://www.flaticon.com/free-icon/do-not-disturb-rounded-sign_61072
 *
 * @since 2.4.0
 *
 * @return string
 */
function get_email_failed_svg() {
	return <<<EOT
<?xml version="1.0" encoding="iso-8859-1"?>
<!-- Generator: Adobe Illustrator 16.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg class="el_sent_status--failed" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="15px" height="15px" viewBox="0 0 510 510" style="enable-background:new 0 0 510 510;" xml:space="preserve">
<g>
	<g id="do-not-disturb">
		<path d="M255,0C114.75,0,0,114.75,0,255s114.75,255,255,255s255-114.75,255-255S395.25,0,255,0z M51,255c0-112.2,91.8-204,204-204
			c45.9,0,89.25,15.3,124.95,43.35l-285.6,285.6C66.3,344.25,51,300.9,51,255z M255,459c-45.9,0-89.25-15.3-124.95-43.35
			L415.65,130.05C443.7,165.75,459,209.1,459,255C459,367.2,367.2,459,255,459z"/>
	</g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
</svg>
EOT;
}

/**
 * Returns the Email sent SVG.
 *
 * @see   https://www.flaticon.com/free-icon/tick-inside-circle_61222
 *
 * @since 2.4.0
 *
 * @return string
 */
function get_email_sent_svg() {
	return <<<EOT
<?xml version="1.0" encoding="iso-8859-1"?>
<!-- Generator: Adobe Illustrator 16.0.0, SVG Export Plug-In . SVG Version: 6.00 Build 0)  -->
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg class="el_sent_status--sent" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 width="15px" height="15px" viewBox="0 0 510 510" style="enable-background:new 0 0 510 510;" xml:space="preserve">
<g>
	<g id="check-circle-outline">
		<path d="M150.45,206.55l-35.7,35.7L229.5,357l255-255l-35.7-35.7L229.5,285.6L150.45,206.55z M459,255c0,112.2-91.8,204-204,204
			S51,367.2,51,255S142.8,51,255,51c20.4,0,38.25,2.55,56.1,7.65l40.801-40.8C321.3,7.65,288.15,0,255,0C114.75,0,0,114.75,0,255
			s114.75,255,255,255s255-114.75,255-255H459z"/>
	</g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
<g>
</g>
</svg>

EOT;

}

/**
 * Gets the log row class by result code.
 *
 * @param int $result Mail sent status.
 *
 * @return string
 */
function get_log_row_class_by_result_code( $result ) {
	$log_row_classes = array(
		0 => 'el_email_sent_status--failed',
		1 => 'el_email_sent_status--sent',
	);
	if ( empty ( $result ) ) {
		return $log_row_classes[0];
	}

	$result = absint( $result );
	if ( array_key_exists( $result, $log_row_classes ) ) {
		return $log_row_classes[ $result ];
	}

	return $log_row_classes[0];
}

/**
 * Returns Comma separated values of the given array elements.
 *
 * Use $delimiter param to join elements other than `,`.
 *
 * @since 2.3.0
 *
 * @param array|string $value     The array whose values are to be joined.
 * @param string       $delimiter Optional. Default is `,`.
 *
 * @return string
 */
function join_array_elements_with_delimiter( $value, $delimiter = ',' ) {
	if ( is_array( $value ) ) {
		return implode( $delimiter, $value );
	}

	return is_string( $value ) ? $value : '';
}

/**
 * Gets the User defined Date time format.
 *
 * @used-by \EmailLog\Core\UI\Setting\CoreSetting
 * @used-by \EmailLog\Util\render_auto_delete_logs_next_run_schedule()
 *
 * @since   2.3.0
 *
 * @return string
 */
function get_user_defined_date_time_format() {
	return sprintf( '%1$s %2$s', get_option( 'date_format', 'Y-m-d' ), get_option( 'time_format', 'g:i a' ) );
}

/**
 * Renders the next run auto delete logs schedule in Date and time format set within WordPress.
 *
 * @used-by \EmailLog\Addon\UI\Setting\DashboardWidget
 * @used-by \EmailLog\Core\UI\Component\AutoDeleteLogsSetting
 *
 * @since   2.3.0
 */
function render_auto_delete_logs_next_run_schedule() {
	?>
	<?php if ( wp_next_scheduled( 'el_scheduled_delete_logs' ) ) : ?>
		<p>
			<?php _e( 'Auto delete logs cron will be triggered next at', 'email-log' ); ?>:
			<?php $date_time_format = get_user_defined_date_time_format(); ?>
			<strong><?php echo get_date_from_gmt( date( 'Y-m-d H:i:s', wp_next_scheduled( 'el_scheduled_delete_logs' ) ), $date_time_format ); ?></strong>
		</p>
	<?php endif; ?>
	<?php
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
 * @since 2.3.2
 * @since 2.3.0
 *
 * @param string $db_column
 *
 * @return string
 */
function get_column_label_by_db_column( $db_column ) {
	// Standard column labels are on the right.
	// $mapping[ $non_standard_key ] => $standard_key
	$mapping = array(
		'to'          => 'to_email', // EmailLog\Core\UI\ListTable::get_columns() uses `to`.
		'reply-to'    => 'reply_to',
		'attachment'  => 'attachments',
		'sent_status' => 'result',
	);

	$labels = get_email_log_columns();

	/**
	 * Filters the Labels used through out the Email Log plugin.
	 *
	 * @since 2.3.0
	 *
	 * @param array $labels {
	 *                      List of DB Columns and its respective labels.
	 *
	 *                      Example:
	 *                      'id'          => __( 'ID', 'email-log' ),
	 *
	 * @type string $key    DB Column or any key for which a Label would be required. Accepts a internationalized string as Label.
	 *              }
	 */
	$labels = apply_filters( 'el_db_column_labels', $labels );

	if ( array_key_exists( $db_column, $labels ) ) {
		return $labels[ $db_column ];
	} else if ( array_key_exists( $db_column, $mapping ) ) {
		$label_key = $mapping[ $db_column ];

		return $labels[ $label_key ];
	}

	return $db_column;
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
function get_email_log_columns() {
	return array(
		'id'          => __( 'ID', 'email-log' ),
		'sent_date'   => __( 'Sent at', 'email-log' ),
		'to_email'    => __( 'To', 'email-log' ),
		'subject'     => __( 'Subject', 'email-log' ),
		'message'     => __( 'Message', 'email-log' ),
		'from'        => __( 'From', 'email-log' ),
		'cc'          => __( 'CC', 'email-log' ),
		'bcc'         => __( 'BCC', 'email-log' ),
		'attachments' => __( 'Attachment', 'email-log' ),
		'ip_address'  => __( 'IP Address', 'email-log' ),
		'reply_to'    => __( 'Reply To', 'email-log' ),
		'result'      => __( 'Sent Status', 'email-log' ),
	);
}

/**
 * Abstract of the core logic behind masking.
 *
 * @since 2.3.2
 *
 * @param string $value     Content.
 * @param string $mask_char Mask character.
 * @param int    $percent   The higher the percent, the more masking character on the email.
 *
 * @return string
 */
function get_masked_value( $value, $mask_char, $percent ) {
	$len        = strlen( $value );
	$mask_count = (int) floor( $len * $percent / 100 );
	$offset     = (int) floor( ( $len - $mask_count ) / 2 );

	return substr( $value, 0, $offset ) . str_repeat( $mask_char, $mask_count ) . substr( $value, $mask_count + $offset );
}

/**
 * Masks Email address.
 *
 * @see   http://www.webhostingtalk.com/showthread.php?t=1014672
 * @since 2.3.2
 *
 * @uses  get_masked_value()
 *
 * @param string $email     Email to be masked.
 * @param string $mask_char Mask character.
 * @param int    $percent   The higher the percent, the more masking character on the email.
 *
 * @return string
 */
function mask_email( $email, $mask_char = '*', $percent = 50 ) {
	if ( ! is_email( $email ) ) {
		return $email;
	}

	list( $user, $domain ) = preg_split( '/@/', $email );

	return sprintf(
		'%1$s@%2$s',
		get_masked_value( $user, $mask_char, $percent ),
		get_masked_value( $domain, $mask_char, $percent )
	);
}

/**
 * Mask Content fields.
 *
 * Content fields can be Subject or Email message.
 *
 * @since 2.3.2
 *
 * @uses  get_masked_value()
 *
 * @param string $content   The actual content.
 * @param string $mask_char Mask character.
 * @param int    $percent   The higher the percent, the more masking character on the email.
 *
 * @return string
 */
function mask_content( $content, $mask_char = '*', $percent = 80 ) {
	$content = wp_strip_all_tags( $content );

	return get_masked_value( $content, $mask_char, $percent );
}
