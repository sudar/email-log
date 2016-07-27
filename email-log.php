<?php
/**
 * Plugin Name: Email Log
 * Plugin URI: http://sudarmuthu.com/wordpress/email-log
 * Description: Logs every email sent through WordPress
 * Donate Link: http://sudarmuthu.com/if-you-wanna-thank-me
 * Author: Sudar
 * Version: 1.9.1
 * Author URI: http://sudarmuthu.com/
 * Text Domain: email-log
 * Domain Path: languages/
 * === RELEASE NOTES ===
 * Check readme file for full release notes
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
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

if ( version_compare( PHP_VERSION, '5.3.0', '<' ) ) {
	/**
	 * v2.0 of the Email Log plugin dropped support for PHP 5.2.
	 * If you are still struck with PHP 5.2 and can't update, then use v1.9.1 of the plugin.
	 * But note that some add-ons may not work.
	 *
	 * @see   http://sudarmuthu.com/blog/why-i-am-dropping-support-for-php-5-2-in-my-wordpress-plugins/
	 *
	 * @since 2.0
	 */
	function email_log_compatibility_notice() {
		?>
		<div class="error">
			<p>
				<?php __( 'Email Log requires at least PHP 5.3 to function properly. Please upgrade PHP or use v1.9.1 of Email Log', 'email-log' ); ?>
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

global $email_log;
$plugin_dir = plugin_dir_path( __FILE__ );

// setup autoloader.
require_once 'include/EmailLogAutoloader.php';

$loader = new \EmailLog\EmailLogAutoloader();
$loader->add_namespace( 'EmailLog', $plugin_dir . 'include' );

if ( file_exists( $plugin_dir . 'tests/' ) ) {
	// if tests are present, then add them.
	$loader->add_namespace( 'EmailLog', $plugin_dir . 'tests/wp-tests' );
}

$loader->add_file( $plugin_dir . 'include/Util/helper.php' );

$loader->register();

$email_log                       = new \EmailLog\Core\EmailLog( __FILE__ );
$email_log->table_manager        = new \EmailLog\Core\DB\TableManager();
$email_log->logger               = new \EmailLog\Core\EmailLogger();
$email_log->plugin_list_enhancer = new \EmailLog\Core\UI\PluginListEnhancer( __FILE__ );

// `register_activation_hook` can't be called from inside any hook.
register_activation_hook( __FILE__, array( $email_log->table_manager, 'on_activate' ) );

// Load the plugin
add_action( 'wp_loaded', array( $email_log, 'load' ) );

/**
 * Return the global instance of Email Log plugin.
 * Eventually the EmailLog class might become singleton.
 *
 * @since 2.0
 *
 * @global EmailLog $email_log
 * @return \EmailLog\Core\EmailLog
 */
function email_log() {
	global $email_log;

	return $email_log;
}
