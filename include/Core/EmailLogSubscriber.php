<?php namespace EmailLog\Core;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * EmailLogSubscriber interface.
 * The `load()` method of this interface will be called by Email Log.
 *
 * @since 2.0.0
 */
interface EmailLogSubscriber {

	/**
	 * This method will be called by Email Log after `wp-loaded` event.
	 *
	 * @return void
	 */
	public function load();
}
