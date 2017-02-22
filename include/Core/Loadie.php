<?php namespace EmailLog\Core;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * EmailLog Loadie interface.
 * The `load()` method of this interface will be called by Email Log.
 * Even though Loadie is not an actual word it sound more logical than subscriber.
 *
 * @since 2.0.0
 */
interface Loadie {

	/**
	 * This method will be called by Email Log after `wp-loaded` event.
	 *
	 * @return void
	 */
	public function load();
}
