<?php namespace EmailLog\Core\Request;

use EmailLog\Core\Loadie;
use EmailLog\Core\UI\Page\LogListPage;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Check nonce for all Email Log requests.
 *
 * @since 2.0.0
 */
class NonceChecker implements Loadie {

	/**
	 * Setup hooks.
	 *
	 * @inheritdoc
	 */
	public function load() {
		add_action( 'admin_init', array( $this, 'check_nonce' ) );
	}

	/**
	 * Check nonce for all Email Log Requests.
	 * All Email Log Requests will have the `el_` prefix and
	 * nonce would be available at `el_{action_name}_nonce`.
	 */
	public function check_nonce() {
		if ( ! isset( $_POST['el-action'] ) && ! isset( $_REQUEST['action'] ) ) {
			return;
		}

		if ( isset( $_POST['el-action'] ) ) {
			$action = sanitize_text_field( $_POST['el-action'] );

			if ( ! isset( $_POST[ $action . '_nonce' ] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( $_POST[ $action . '_nonce' ], $action ) ) {
				return;
			}
		}

		if ( isset( $_REQUEST['action'] ) ) {
			$action = sanitize_text_field( $_REQUEST['action'] );

			if ( 'el-log-list-' !== substr( $action, 0, 12 ) ) {
				return;
			}

			if ( ! isset( $_REQUEST[ LogListPage::LOG_LIST_ACTION_NONCE_FIELD ] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( $_REQUEST[ LogListPage::LOG_LIST_ACTION_NONCE_FIELD ], LogListPage::LOG_LIST_ACTION_NONCE ) ) {
				return;
			}
		}

		/**
		 * Perform `el` action.
		 * Nonce check has already happened at this point.
		 *
		 * @since 2.0.0
		 *
		 * @param string $action   Action name.
		 * @param array  $_REQUEST Request data.
		 */
		do_action( 'el_action', $action, $_REQUEST );

		/**
		 * Perform `el` action.
		 * Nonce check has already happened at this point.
		 *
		 * @since 2.0.0
		 *
		 * @param array $_REQUEST Request data.
		 */
		do_action( $action, $_REQUEST );
	}
}
