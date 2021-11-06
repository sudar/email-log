<?php
/**
 * Plugin Name: Email Log
 * Plugin URI: https://wpemaillog.com
 * Description: Logs every email sent through WordPress
 * Donate Link: http://sudarmuthu.com/if-you-wanna-thank-me
 * Author: Sudar
 * Version: 2.4.8
 * Author URI: http://sudarmuthu.com/
 * Text Domain: email-log
 * Domain Path: languages/
 * === RELEASE NOTES ===
 * Check readme file for full release notes.
 */

/**
 * Copyright 2009  Sudar Muthu  (email : sudar@sudarmuthu.com)
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA.
 */
defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

// Include the stub of the old `EmailLog` class, so that old add-ons don't generate a fatal error.
require_once plugin_dir_path( __FILE__ ) . 'include/compatibility/EmailLog.php';

if ( version_compare( PHP_VERSION, '5.6.0', '<' ) ) {
	/**
	 * Version 2.4.0 of the Email Log plugin dropped support for PHP 5.3 to PHP 5.5.
	 *
	 * Version 2.0 of the Email Log plugin dropped support for PHP 5.2.
	 * If you are still struck with PHP 5.2 and can't update, then use v1.9.1 of the plugin.
	 * But note that some add-ons may not work.
	 *
	 * @see   http://sudarmuthu.com/blog/why-i-am-dropping-support-for-php-5-2-in-my-wordpress-plugins/
	 * @since 2.0
	 */
	function email_log_compatibility_notice() {
		?>
		<div class="error">
			<p>
				<?php
				printf(
					__( 'Email Log requires at least PHP 5.6 to function properly. Please upgrade PHP or use <a href="%s" target="_blank" rel="noopener">v1.9.1 of Email Log</a>.', 'email-log' ), // @codingStandardsIgnoreLine
					'https://downloads.wordpress.org/plugin/email-log.1.9.1.zip'
				);
				?>
			</p>
		</div>
		<?php
	}

	add_action( 'admin_notices', 'email_log_compatibility_notice' );

	/**
	 * Deactivate Email Log.
	 *
	 * @since 2.0
	 */
	function email_log_deactivate() {
		deactivate_plugins( plugin_basename( __FILE__ ) );
	}

	add_action( 'admin_init', 'email_log_deactivate' );

	return;
}

// PHP is at least 5.6, so we can safely include namespace code.
require_once 'load-email-log.php';
load_email_log( __FILE__ );

// Fix compatibility issues with wpmandrill plugin.
require_once plugin_dir_path( __FILE__ ) . 'include/compatibility/wpmandrill.php';
