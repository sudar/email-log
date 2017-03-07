<?php namespace EmailLog\Core\Request;

use EmailLog\Core\Loadie;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * RequestHandler for Email Log requests.
 *
 * @since 2.0.0
 */
class RequestHandler implements Loadie {

	/**
	 * Setup hooks.
	 *
	 * @inheritdoc
	 */
	public function load() {
		add_action( 'admin_init', array( $this, 'handle_request' ) );
	}

	/**
	 * Check nonce for all Email Log Requests.
	 * All Email Log Requests will have the `el_` prefix and
	 * nonce would be available at `el_{action_name}_nonce`.
	 */
	public function handle_request() {
		if ( ! isset( $_POST['el-action'] ) ) {
			return;
		}

		$action = sanitize_text_field( $_POST['el-action'] );

		if ( ! isset( $_POST[ $action . '_nonce' ] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST[ $action . '_nonce' ], $action ) ) {
			return;
		}

		/**
		 * Perform `el` action.
		 * Nonce check has already happened at this point.
		 *
		 * @since 2.0
		 *
		 * @param string $action Action name.
		 * @param array  $_POST  Request data.
		 */
		do_action( 'el_action', $action, $_POST );

		/**
		 * Perform `el` action.
		 * Nonce check has already happened at this point.
		 *
		 * @since 2.0
		 *
		 * @param array $_POST Request data.
		 */
		do_action( $action, $_POST );
	}
}
