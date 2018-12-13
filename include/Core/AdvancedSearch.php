<?php namespace EmailLog\Core;

use EmailLog\Util;

/**
 * Class AdvancedSearch
 * @package EmailLog\Core
 *
 * @since 2.3.0
 */
class AdvancedSearch implements Loadie {
	public function load() {
		add_action( 'el-log-list-adv-search', array( $this, 'handle_advanced_search' ) );
	}

	public function handle_advanced_search( $request ) {
		// Nonce verification handled in {@see \EmailLog\Core\Request\NonceChecker::check_nonce()}
		$to = Util\el_array_get( $request, 'to' );
	}
}