<?php namespace EmailLog\Core\UI\Page;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * System Info Page.
 * This page is displayed about ststem info.
 *
 * @since 2.0.0
 */
class SystemInfoPage extends BasePage {
	const PAGE_SLUG = 'system_infos';

	/**
	 * Capability to manage system info.
	 *
	 * @since 2.3.0
	 */
	const CAPABILITY = 'manage_system_infos';

	/**
	 * Specify additional hooks.
	 *
	 * @inheritdoc
	 */
	public function load() {
		parent::load();

		$this->messages = array(
			'info_message' => __( 'Please include this information when posting support requests.', 'email-log' ),
		);

		$this->actions = array( 'download_sysinfo' );
		add_action( 'el_download_sysinfo', array( $this, 'generate_sysinfo_download' ) );

		add_action( 'admin_init', array( $this, 'request_handler' ) );
		add_filter( 'el_action_nonce_check', array( $this, 'verify_nonce' ), 10, 2 );
	}

	/**
	 * Check for nonce before executing the action.
	 *
	 * @param bool   $result The current result.
	 * @param string $action Action name.
	 *
	 * @return bool True if nonce is verified, False otherwise.
	 */
	public function verify_nonce( $result, $action ) {
		/**
		 * List of actions for page.
		 *
		 * @param array    $actions Actions.
		 * @param BasePage $page    Page objects.
		 *
		 * @since 2.3.0
		 */
		$page_actions = apply_filters( 'el_page_actions', $this->actions, $this );

		if ( in_array( $action, $page_actions, true ) ) {
			if ( check_admin_referer( 'el-{self::PAGE_SLUG}', 'el-{self::PAGE_SLUG}-nonce' ) ) {
				return true;
			}
		}

		return $result;
	}

	/**
	 * Register page.
	 */
	public function register_page() {

		$this->page = add_submenu_page(
			LogListPage::PAGE_SLUG,
			__( 'System Info', 'email-log' ),
			__( 'System Info', 'email-log' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);

		add_action( "load-{$this->page}", array( $this, 'render_help_tab' ) );
	}

	/**
	 * Get current theme name.
	 *
	 * @return string Current theme name.
	 */
	protected function el_get_current_theme_name() {
		if ( get_bloginfo( 'version' ) < '3.4' ) {
			$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );

			return $theme_data['Name'] . ' ' . $theme_data['Version'];
		} else {
			$theme_data = wp_get_theme();

			return $theme_data->Name . ' ' . $theme_data->Version;
		}
	}

	/**
	 * Try to identity the hosting provider.
	 *
	 * @return string Web host name if identified, empty string otherwise.
	 */
	protected function el_identify_host() {
		$host = '';
		if ( defined( 'WPE_APIKEY' ) ) {
			$host = 'WP Engine';
		} elseif ( defined( 'PAGELYBIN' ) ) {
			$host = 'Pagely';
		}

		return $host;
	}

	/**
	 * Print plugins that are currently active.
	 */
	protected function el_print_current_plugins() {
		$plugins        = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $plugins as $plugin_path => $plugin ) {
			// If the plugin isn't active, don't show it.
			if ( ! in_array( $plugin_path, $active_plugins ) ) {
				continue;
			}

			echo $plugin['Name'] . ': ' . $plugin['Version'] . "\n";
		}
	}

	/**
	 * Print network active plugins.
	 */
	protected function el_print_network_active_plugins() {
		$plugins        = wp_get_active_network_plugins();
		$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

		foreach ( $plugins as $plugin_path ) {
			$plugin_base = plugin_basename( $plugin_path );

			// If the plugin isn't active, don't show it.
			if ( ! array_key_exists( $plugin_base, $active_plugins ) ) {
				continue;
			}

			$plugin = get_plugin_data( $plugin_path );

			echo $plugin['Name'] . ' :' . $plugin['Version'] . "\n";
		}
	}

	protected function el_plugin_version() {
		$plugin_path = WP_PLUGIN_DIR . '/email-log/email-log.php';
		$plugin_data = get_plugin_data( $plugin_path );
		echo $plugin_data['Version'];
	}

	/**
	 * Render the page.
	 */
	public function render_page() {
		global $wpdb;
		?>
		<form method = "post">
		<div class="updated">
			<p><strong><?php echo $this->messages['info_message']; ?></strong></p>
		</div>

		<?php if ( defined( 'SAVEQUERIES' ) && SAVEQUERIES ) { ?>
			<div class="notice notice-warning">
				<p><strong>
					<?php printf( __( 'SAVEQUERIES is <a href="%s" target="_blank">enabled</a>. This puts additional load on the memory and will restrict the number of items that can be deleted.', 'bulk-delete' ), 'https://codex.wordpress.org/Editing_wp-config.php#Save_queries_for_analysis' ); ?>
				</strong></p>
			</div>
		<?php } ?>

		<?php if ( defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON ) { ?>
			<div class="notice notice-warning">
				<p><strong>
					<?php printf( __( 'DISABLE_WP_CRON is <a href="%s" target="_blank">enabled</a>. This prevents scheduler from running.', 'bulk-delete' ), 'https://codex.wordpress.org/Editing_wp-config.php#Disable_Cron_and_Cron_Timeout' ); ?>
				</strong></p>
			</div>
		<?php } ?>
		<div class="wrap">
			<h1><?php _e( 'Email Log  - System Info', 'email-log' ); ?></h1>

		<textarea wrap="off" style="width:100%;height:500px;font-family:Menlo,Monaco,monospace;white-space:pre;" readonly="readonly" onclick="this.focus();this.select()" id="system-info-textarea" name="email-log-sysinfo" title="<?php _e( 'To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'bulk-delete' ); ?>">
### Begin System Info ###
		<?php
		/**
		 * Runs before displaying system info.
		 *
		 * This action is primarily for adding extra content in System Info.
		 */
		do_action( 'el_system_info_before' );
		?>

Multisite:                <?php echo is_multisite() ? 'Yes' . "\n" : 'No' . "\n"; ?>

SITE_URL:                 <?php echo site_url() . "\n"; ?>
HOME_URL:                 <?php echo home_url() . "\n"; ?>
Browser:                  <?php echo esc_html( $_SERVER['HTTP_USER_AGENT'] ), "\n"; ?>

Permalink Structure:      <?php echo get_option( 'permalink_structure' ) . "\n"; ?>
Active Theme:             <?php echo $this->el_get_current_theme_name() . "\n"; ?>
		<?php
		$host = $this->el_identify_host();
		if ( '' !== $host ) :
			?>
Host:                     <?php echo $host . "\n\n"; ?>
<?php endif; ?>

		<?php $post_types = get_post_types(); ?>
Registered Post types:    <?php echo implode( ', ', $post_types ) . "\n"; ?>
		<?php
		foreach ( $post_types as $post_type ) {
			echo $post_type;
			if ( strlen( $post_type ) < 26 ) {
				echo str_repeat( ' ', 26 - strlen( $post_type ) );
			}
			$post_count = wp_count_posts( $post_type );
			foreach ( $post_count as $key => $value ) {
				echo $key, '=', $value, ', ';
			}
			echo "\n";
		}
		?>

		<?php $taxonomies = get_taxonomies(); ?>
Registered Taxonomies:    <?php echo implode( ', ', $taxonomies ) . "\n"; ?>

Email log Version:        <?php $this->el_plugin_version() . "\n"; ?>
WordPress Version:        <?php echo get_bloginfo( 'version' ) . "\n"; ?>
PHP Version:              <?php echo PHP_VERSION . "\n"; ?>
MySQL Version:            <?php echo $wpdb->db_version() . "\n"; ?>
Web Server Info:          <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>

WordPress Memory Limit:   <?php echo WP_MEMORY_LIMIT; ?><?php echo "\n"; ?>
WordPress Max Limit:      <?php echo WP_MAX_MEMORY_LIMIT; ?><?php echo "\n"; ?>
PHP Memory Limit:         <?php echo ini_get( 'memory_limit' ) . "\n"; ?>

SAVEQUERIES:              <?php echo defined( 'SAVEQUERIES' ) ? SAVEQUERIES ? 'Enabled' . "\n" : 'Disabled' . "\n" : 'Not set' . "\n"; ?>
WP_DEBUG:                 <?php echo defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' . "\n" : 'Disabled' . "\n" : 'Not set' . "\n"; ?>
WP_SCRIPT_DEBUG:          <?php echo defined( 'WP_SCRIPT_DEBUG' ) ? WP_SCRIPT_DEBUG ? 'Enabled' . "\n" : 'Disabled' . "\n" : 'Not set' . "\n"; ?>

GMT Offset:               <?php echo esc_html( get_option( 'gmt_offset' ) ), "\n\n"; ?>
DISABLE_WP_CRON:          <?php echo defined( 'DISABLE_WP_CRON' ) ? DISABLE_WP_CRON ? 'Yes' . "\n" : 'No' . "\n" : 'Not set' . "\n"; ?>
WP_CRON_LOCK_TIMEOUT:     <?php echo defined( 'WP_CRON_LOCK_TIMEOUT' ) ? WP_CRON_LOCK_TIMEOUT : 'Not set', "\n"; ?>
EMPTY_TRASH_DAYS:         <?php echo defined( 'EMPTY_TRASH_DAYS' ) ? EMPTY_TRASH_DAYS : 'Not set', "\n"; ?>

PHP Safe Mode:            <?php echo ini_get( 'safe_mode' ) ? 'Yes' : 'No', "\n"; // phpcs:ignore PHPCompatibility.PHP.DeprecatedIniDirectives.safe_modeDeprecatedRemoved ?>
PHP Upload Max Size:      <?php echo ini_get( 'upload_max_filesize' ) . "\n"; ?>
PHP Post Max Size:        <?php echo ini_get( 'post_max_size' ) . "\n"; ?>
PHP Upload Max Filesize:  <?php echo ini_get( 'upload_max_filesize' ) . "\n"; ?>
PHP Time Limit:           <?php echo ini_get( 'max_execution_time' ) . "\n"; ?>
PHP Max Input Vars:       <?php echo ini_get( 'max_input_vars' ) . "\n"; // phpcs:ignore PHPCompatibility.PHP.NewIniDirectives.max_input_varsFound ?>
PHP Arg Separator:        <?php echo ini_get( 'arg_separator.output' ) . "\n"; ?>
PHP Allow URL File Open:  <?php echo ini_get( 'allow_url_fopen' ) ? 'Yes' : 'No', "\n"; ?>

WP Table Prefix:          <?php echo $wpdb->prefix, "\n"; ?>

Session:                  <?php echo isset( $_SESSION ) ? 'Enabled' : 'Disabled'; ?><?php echo "\n"; ?>
Session Name:             <?php echo esc_html( ini_get( 'session.name' ) ); ?><?php echo "\n"; ?>
Cookie Path:              <?php echo esc_html( ini_get( 'session.cookie_path' ) ); ?><?php echo "\n"; ?>
Save Path:                <?php echo esc_html( ini_get( 'session.save_path' ) ); ?><?php echo "\n"; ?>
Use Cookies:              <?php echo ini_get( 'session.use_cookies' ) ? 'On' : 'Off'; ?><?php echo "\n"; ?>
Use Only Cookies:         <?php echo ini_get( 'session.use_only_cookies' ) ? 'On' : 'Off'; ?><?php echo "\n"; ?>

DISPLAY ERRORS:           <?php echo ( ini_get( 'display_errors' ) ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A'; ?><?php echo "\n"; ?>
FSOCKOPEN:                <?php echo ( function_exists( 'fsockopen' ) ) ? 'Your server supports fsockopen.' : 'Your server does not support fsockopen.'; ?><?php echo "\n"; ?>
cURL:                     <?php echo ( function_exists( 'curl_init' ) ) ? 'Your server supports cURL.' : 'Your server does not support cURL.'; ?><?php echo "\n"; ?>
SOAP Client:              <?php echo ( class_exists( 'SoapClient' ) ) ? 'Your server has the SOAP Client enabled.' : 'Your server does not have the SOAP Client enabled.'; ?><?php echo "\n"; ?>
SUHOSIN:                  <?php echo ( extension_loaded( 'suhosin' ) ) ? 'Your server has SUHOSIN installed.' : 'Your server does not have SUHOSIN installed.'; ?><?php echo "\n"; ?>

ACTIVE PLUGINS:

		<?php $this->el_print_current_plugins(); ?>

		<?php
		if ( is_multisite() ) :
			?>
NETWORK ACTIVE PLUGINS:

			<?php
			$this->el_print_network_active_plugins();
		endif;
		?>

		<?php do_action( 'el_system_info_after' ); ?>
### End System Info ###</textarea>

		<p class="submit">
			<input type="hidden" name="el_action" value="download_sysinfo">
			<?php submit_button( 'Download System Info File', 'primary', 'email-log-sysinfo-button', false ); ?>
		</p>


		</div>
	</form>
		<?php

		$this->render_page_footer();
	}

	/**
	 * Generates the System Info Download File.
	 */
	public function generate_sysinfo_download() {
		nocache_headers();

		header( 'Content-type: text/plain' );
		header( 'Content-Disposition: attachment; filename="email-log-system-info.txt"' );

		echo wp_strip_all_tags( $_POST['email-log-sysinfo'] );
		die();
	}

	/**
	 * Handle both POST and GET requests.
	 * This method automatically triggers all the actions after checking the nonce.
	 */
	public function request_handler() {
		if ( isset( $_POST['el_action'] ) ) {
			$el_action = sanitize_text_field( $_POST['el_action'] );

			/**
			 * Perform the operation.
			 * This hook is for doing the operation. Nonce check has already happened by this point.
			 *
			 * @since 2.3.0
			 */
			do_action( 'el_' . $el_action, $_POST );
		}
	}

}
