<?php
/**
 * Fixes compatibility issues with wpmandrill plugin.
 *
 * The wpmandrill plugin is changing "message" key to "html".
 *
 * @see https://github.com/sudar/email-log/issues/20
 *
 * Ideally this should be fixed in wpmandrill, but I am including this hack here till it is fixed by them.
 * This will be eventually removed once it is fixed in the original plugin.
 * @since 2.3.2
 */
defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Fix compatiblity issue with wpmandrill plguin.
 * The wpmandrill plugin is changing "message" key to "html".
 *
 * @since 2.3.2
 *
 * @param array $log       Log that is going to be inserted.
 * @param array $mail_info Original mail info that was sent.
 *
 * @return array Modified log.
 */
function el_fix_compatibility_with_wpmandrill( $log, $mail_info ) {
	if ( ! empty( $log['message'] ) ) {
		return $log;
	}

	if ( isset( $mail_info['html'] ) && ! empty( $mail_info['html'] ) ) {
		$log['message'] = $mail_info['html'];
	}

	return $log;
}
add_filter( 'el_email_log_before_insert', 'el_fix_compatibility_with_wpmandrill', 10, 2 );
