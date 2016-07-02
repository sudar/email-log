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

/**
 * Plugin Root File
 *
 * @since 1.7.2
 */
if ( ! defined( 'EMAIL_LOG_PLUGIN_FILE' ) ) {
	define( 'EMAIL_LOG_PLUGIN_FILE', __FILE__ );
}

/**
 * Handles installation and table creation.
 */
require_once plugin_dir_path( __FILE__ ) . 'include/install.php';

/**
 * Helper functions.
 */
require_once plugin_dir_path( __FILE__ ) . 'include/util/helper.php';

/**
 * The main plugin class.
 *
 * @since Genesis
 */
class EmailLog {

	/**
	 * Filesystem directory path with trailing slash.
	 *
	 * @since Genesis
	 * @var string $include_path
	 */
	public $include_path;

	/**
	 * Admin screen object.
	 *
	 * @since Genesis
	 * @access private
	 * @var string $include_path
	 */
	private $admin_screen;

	/**
	 * Version number.
	 *
	 * @since Genesis
	 * @var const VERSION
	 */
	const VERSION                  = '1.9.1';

	/**
	 * Filter name.
	 *
	 * @since Genesis
	 * @var const FILTER_NAME
	 */
	const FILTER_NAME              = 'wp_mail_log';

	/**
	 * Page slug to be used in admin dashboard hyperlinks.
	 *
	 * @since Genesis
	 * @var const PAGE_SLUG
	 */
	const PAGE_SLUG                = 'email-log';

	/**
	 * String value to generate nonce.
	 *
	 * @since Genesis
	 * @var const DELETE_LOG_NONCE_FIELD
	 */
	const DELETE_LOG_NONCE_FIELD   = 'sm-delete-email-log-nonce';

	/**
	 * String value to generate nonce.
	 *
	 * @since Genesis
	 * @var const DELETE_LOG_ACTION
	 */
	const DELETE_LOG_ACTION        = 'sm-delete-email-log';

	// DB stuff
	const TABLE_NAME               = 'email_log';          /* Database table name */
	const DB_OPTION_NAME           = 'email-log-db';       /* Database option name */
	const DB_VERSION               = '0.1';                /* Database version */

	// JS Stuff
	const JS_HANDLE                = 'email-log';

	//hooks
	const HOOK_LOG_COLUMNS         = 'email_log_manage_log_columns';
	const HOOK_LOG_DISPLAY_COLUMNS = 'email_log_display_log_columns';

	/**
	 * Initialize the plugin by registering the hooks.
	 */
	function __construct() {
		$this->include_path = plugin_dir_path( __FILE__ );

		// Load localization domain.
		$this->translations = dirname( plugin_basename( __FILE__ ) ) . '/languages/' ;
		load_plugin_textdomain( 'email-log', false, $this->translations );

		// Register hooks.
		add_action( 'admin_menu', array( $this, 'register_settings_page' ) );

		// Register Filter.
		add_filter( 'wp_mail', array( $this, 'log_email' ) );
		add_filter( 'set-screen-option', array( $this, 'save_screen_options' ), 10, 3 );
		add_filter( 'plugin_row_meta', array( $this, 'add_plugin_links' ), 10, 2 );

		$plugin = plugin_basename( __FILE__ );
		add_filter( "plugin_action_links_$plugin", array( $this, 'add_action_links' ) );

		// Add our ajax call.
		add_action( 'wp_ajax_display_content', array( $this, 'display_content_callback' ) );
	}

	/**
	 * Adds additional links in the plugin listing page.
	 *
	 * @since Genesis
	 *
	 * @see Additional links in the Plugin listing is based on
	 * @link http://zourbuth.com/archives/751/creating-additional-wordpress-plugin-links-row-meta/
	 *
	 * @param array $links Array with default links to display in plugins page.
	 * @param string $file The name of the plugin file.
	 * @return array Array with links to display in plugins page.
	 */
	public function add_plugin_links( $links, $file ) {
		$plugin = plugin_basename( __FILE__ );

		if ( $file == $plugin ) {
			// only for this plugin
			return array_merge( $links,
				array( '<a href="http://sudarmuthu.com/wordpress/email-log/pro-addons" target="_blank">' . __( 'Buy Addons', 'email-log' ) . '</a>' )
			);
		}
		return $links;
	}

	/**
	 * Registers the settings page.
	 *
	 * @since Genesis
	 */
	public function register_settings_page() {
		// Save the handle to your admin page - you'll need it to create a WP_Screen object
		$this->admin_page = add_submenu_page( 'tools.php', __( 'Email Log', 'email-log' ), __( 'Email Log', 'email-log' ), 'manage_options', self::PAGE_SLUG , array( $this, 'display_logs' ) );

		add_action( "load-{$this->admin_page}", array( $this, 'create_settings_panel' ) );
	}

	/**
	 * Displays the stored email log.
	 *
	 * @since Genesis
	 */
	public function display_logs() {
		add_thickbox();

		$this->logs_table->prepare_items( $this->get_per_page() );
?>
	<div class="wrap">
		<h2><?php _e( 'Email Logs', 'email-log' );?></h2>
		<?php
		if ( isset( $this->logs_deleted ) && $this->logs_deleted != '' ) {
			$logs_deleted = intval( $this->logs_deleted );

			if ( $logs_deleted > 0 ) {
				echo '<div class="updated"><p>' . sprintf( _n( '1 email log deleted.', '%s email logs deleted', $logs_deleted, 'email-log' ), $logs_deleted ) . '</p></div>';
			} else {
				echo '<div class="updated"><p>' . __( 'There was some problem in deleting the email logs' , 'email-log' ) . '</p></div>';
			}
			unset( $this->logs_deleted );
		}
?>
		<form id="email-logs-search" method="get">
			<input type="hidden" name="page" value="<?php echo self::PAGE_SLUG; ?>" >
<?php
		$this->logs_table->search_box( __( 'Search Logs', 'email-log' ), 'search_id' );
?>
		</form>

		<form id="email-logs-filter" method="get">
			<input type="hidden" name="page" value="<?php echo $_REQUEST['page'] ?>" />
<?php
		wp_nonce_field( self::DELETE_LOG_ACTION, self::DELETE_LOG_NONCE_FIELD );
		$this->logs_table->display();
?>
		</form>
	</div>
<?php
		/**
		 * Action to add additional content to email log admin footer.
		 *
		 * @since 1.8
		 */
		do_action( 'el_admin_footer' );

		// Display credits in Footer
		add_action( 'in_admin_footer', array( $this, 'add_footer_links' ) );
	}

	/**
	 * Adds settings panel for the plugin.
	 *
	 * @since Genesis
	 */
	public function create_settings_panel() {

		/**
		 * Create the WP_Screen object against your admin page handle
		 * This ensures we're working with the right admin page
		 */
		$this->admin_screen = WP_Screen::get( $this->admin_page );

		/**
		 * Content specified inline
		 */
		$this->admin_screen->add_help_tab(
			array(
				'title'    => __( 'About Plugin', 'email-log' ),
				'id'       => 'about_tab',
				'content'  => '<p>' . __( 'Email Log WordPress Plugin, allows you to log all emails that are sent through WordPress.', 'email-log' ) . '</p>',
				'callback' => false,
			)
		);

		// Add help sidebar
		$this->admin_screen->set_help_sidebar(
			'<p><strong>' . __( 'More information', 'email-log' ) . '</strong></p>' .
			'<p><a href = "http://sudarmuthu.com/wordpress/email-log">' . __( 'Plugin Homepage/support', 'email-log' ) . '</a></p>' .
			'<p><a href = "http://sudarmuthu.com/blog">' . __( "Plugin author's blog", 'email-log' ) . '</a></p>' .
			'<p><a href = "http://sudarmuthu.com/wordpress/">' . __( "Other Plugin's by Author", 'email-log' ) . '</a></p>'
		);

		// Add screen options
		$this->admin_screen->add_option(
			'per_page',
			array(
				'label' => __( 'Entries per page', 'email-log' ),
				'default' => 20,
				'option' => 'per_page',
			)
		);

		if ( ! class_exists( 'WP_List_Table' ) ) {
			require_once ABSPATH . WPINC . '/class-wp-list-table.php';
		}

		if ( ! class_exists( 'Email_Log_List_Table' ) ) {
			require_once $this->include_path . 'include/class-email-log-list-table.php';
		}

		//Prepare Table of elements
		$this->logs_table = new Email_Log_List_Table();
	}

	/**
	 * AJAX callback for displaying email content.
	 *
	 * @since 1.6
	 */
	public function display_content_callback() {
		global $wpdb;

		if ( current_user_can( 'manage_options' ) ) {
			$table_name = $wpdb->prefix . self::TABLE_NAME;
			$email_id   = absint( $_GET['email_id'] );

			$query   = $wpdb->prepare( 'SELECT * FROM ' . $table_name . ' WHERE id = %d', $email_id );
			$content = $wpdb->get_results( $query );

			echo wpautop( $content[0]->message );
		}

		die(); // this is required to return a proper result
	}

	/**
	 * Saves Screen options.
	 *
	 * @since Genesis
	 *
	 * @param bool|int $status Screen option value. Default false to skip.
	 * @param string   $option The option name.
	 * @param int      $value  The number of rows to use.
	 * @return bool|int
	 */
	function save_screen_options( $status, $option, $value ) {
		if ( 'per_page' == $option ) {
			return $value;
		} else {
			return $status;
		}
	}

	/**
	 * Gets the per page option.
	 *
	 * @since Genesis
	 *
	 * @return int Number of logs a user wanted to be displayed in a page.
	 */
	public static function get_per_page() {
		$screen = get_current_screen();
		$option = $screen->get_option( 'per_page', 'option' );

		$per_page = get_user_meta( get_current_user_id(), $option, true );

		if ( empty( $per_page ) || $per_page < 1 ) {
			$per_page = $screen->get_option( 'per_page', 'default' );
		}

		return $per_page;
	}

	/**
	 * Adds additional links.
	 *
	 * @since Genesis
	 *
	 * @param array $links
	 * @return array
	 */
	public function add_action_links( $links ) {
		// Add a link to this plugin's settings page
		$settings_link = '<a href="tools.php?page=email-log">' . __( 'Log', 'email-log' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Adds Footer links.
	 *
	 * @since Genesis
	 *
	 * @see Function relied on
	 * @link http://striderweb.com/nerdaphernalia/2008/06/give-your-wordpress-plugin-credit/
	 */
	public function add_footer_links() {
		$plugin_data = get_plugin_data( __FILE__ );
		printf( '%1$s ' . __( 'plugin', 'email-log' ) . ' | ' . __( 'Version', 'email-log' ) . ' %2$s | ' . __( 'by', 'email-log' ) . ' %3$s<br />', $plugin_data['Title'], $plugin_data['Version'], $plugin_data['Author'] );
	}

	/**
	 * Logs email to database.
	 *
	 * @since Genesis
	 *
	 * @global object $wpdb
	 *
	 * @param array $mail_info Information about email.
	 * @return array Information about email.
	 */
	public function log_email( $mail_info ) {
		global $wpdb;

		$attachment_present = ( count( $mail_info['attachments'] ) > 0 ) ? 'true' : 'false';

		// return filtered array
		$mail_info  = apply_filters( self::FILTER_NAME, $mail_info );
		$table_name = $wpdb->prefix . self::TABLE_NAME;

		if ( isset( $mail_info['message'] ) ) {
			$message = $mail_info['message'];
		} else {
			// wpmandrill plugin is changing "message" key to "html". See https://github.com/sudar/email-log/issues/20
			// Ideally this should be fixed in wpmandrill, but I am including this hack here till it is fixed by them.
			if ( isset( $mail_info['html'] ) ) {
				$message = $mail_info['html'];
			} else {
				$message = '';
			}
		}

		// Log into the database
		$wpdb->insert( $table_name, array(
				'to_email'    => is_array( $mail_info['to'] ) ? implode( ',', $mail_info['to'] ) : $mail_info['to'],
				'subject'     => $mail_info['subject'],
				'message'     => $message,
				'headers'     => is_array( $mail_info['headers'] ) ? implode( "\n", $mail_info['headers'] ) : $mail_info['headers'],
				'attachments' => $attachment_present,
				'sent_date'   => current_time( 'mysql' ),
			) );

		return $mail_info;
	}
}

/**
 * Instantiates the plugin class
 *
 * @since Genesis
 *
 * @see Class `EmailLog`
 * @global EmailLog $EmailLog
 */
function email_log() {
	global $EmailLog;
	$EmailLog = new EmailLog();
}
add_action( 'init', 'email_log' );
?>
