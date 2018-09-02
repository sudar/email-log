<?php

namespace EmailLog\Core\UI\Component;

/**
 * Shows and generates the System Info file.
 *
 * This will be moved into a seperate repo as a library.
 *
 * Greatly inspired (and shares code) from the system info component in Easy Digital Downloads plugin.
 *
 * @since 2.3.0
 */
class SystemInfo {
	/**
	 * Plugin slug.
	 *
	 * @var string
	 */
	protected $plugin_slug = '';

	/**
	 * Config that controls which sections should be displayed.
	 *
	 * @var array
	 */
	protected $config = array();

	/**
	 * SystemInfo constructor.
	 *
	 * @param string $plugin_slug Slug of the plugin.
	 * @param array  $config      (Optional) Configuration options.
	 *
	 * @see SystemInfo::get_default_config for the list of default config information.
	 */
	public function __construct( $plugin_slug, $config = array() ) {
		$this->plugin_slug = $plugin_slug;
		$this->config      = wp_parse_args( $config, $this->get_default_config() );
	}

	/**
	 * Get Default configuration.
	 *
	 * @return array Default configuration.
	 */
	protected function get_default_config() {
		return array(
			'show_post_types'      => true,
			'show_taxonomies'      => true,
			'show_plugins'         => true,
			'show_network_plugins' => true,
		);
	}

	/**
	 * Render system info.
	 *
	 * PHPCS is disabled for this function since aligned will mess up the system info output.
	 * phpcs:disable
	 */
	public function render() {
		global $wpdb;

		?>
		<textarea wrap="off" readonly="readonly" name="<?php echo esc_attr( $this->plugin_slug ); ?>-system-info"
		          style="font-family:Menlo,Monaco,monospace; white-space:pre; width:100%; height:500px;" onclick="this.focus();this.select()"
		          title="<?php _e( 'To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'email-log' ); ?>">
### Begin System Info ###

<?php
/**
 * Runs before displaying system info.
 *
 * This action is primarily for adding extra content in System Info.
 *
 * @param string $plugin_name Plugin slug.
 */
do_action( 'system_info_before', $this->plugin_slug );
?>

Multisite:                <?php echo is_multisite() ? 'Yes' . "\n" : 'No' . "\n"; ?>

SITE_URL:                 <?php echo site_url() . "\n"; ?>
HOME_URL:                 <?php echo home_url() . "\n"; ?>
Browser:                  <?php echo esc_html( $_SERVER['HTTP_USER_AGENT'] ), "\n"; ?>

Permalink Structure:      <?php echo get_option( 'permalink_structure' ) . "\n"; ?>
Active Theme:             <?php echo $this->get_current_theme_name() . "\n"; ?>
<?php
$host = $this->identify_host();
if ( ! empty( $host ) ) : ?>
Host:                     <?php echo $host . "\n\n"; ?>
<?php endif; ?>

<?php if ( $this->config['show_post_types'] ) : ?>
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
<?php endif; ?>

<?php if ( $this->config['show_taxonomies'] ) : ?>
<?php $taxonomies = get_taxonomies(); ?>
Registered Taxonomies:    <?php echo implode( ', ', $taxonomies ) . "\n"; ?>
<?php endif; ?>

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

PHP Safe Mode:            <?php echo ini_get( 'safe_mode' ) ? 'Yes' : 'No', "\n"; // phpcs:ignore PHPCompatibility.PHP.DeprecatedIniDirectives.safe_modeDeprecatedRemoved?>
PHP Upload Max Size:      <?php echo ini_get( 'upload_max_filesize' ) . "\n"; ?>
PHP Post Max Size:        <?php echo ini_get( 'post_max_size' ) . "\n"; ?>
PHP Upload Max Filesize:  <?php echo ini_get( 'upload_max_filesize' ) . "\n"; ?>
PHP Time Limit:           <?php echo ini_get( 'max_execution_time' ) . "\n"; ?>
PHP Max Input Vars:       <?php echo ini_get( 'max_input_vars' ) . "\n"; // phpcs:ignore PHPCompatibility.PHP.NewIniDirectives.max_input_varsFound?>
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

<?php if ( $this->config['show_plugins'] ) : ?>
ACTIVE PLUGINS:

<?php $this->print_current_plugins(); ?>
<?php endif;?>

<?php if ( $this->config['show_network_plugins'] ) : ?>
<?php if ( is_multisite() ) : ?>
NETWORK ACTIVE PLUGINS:

<?php $this->print_network_active_plugins(); ?>
<?php endif;?>
<?php endif;?>

<?php
/**
 * Runs after displaying system info.
 *
 * This action is primarily for adding extra content in System Info.
 *
 * @param string $plugin_name Plugin slug.
 */
do_action( 'system_info_after', $this->plugin_slug ); ?>
### End System Info ###</textarea>
		<?php
	}
	// phpcs:enable

	/**
	 * Download System info as a file.
	 *
	 * @param string $file_name (Optional)Name of the file. Default is {plugin slug}-system-info.txt.
	 */
	public function download_as_file( $file_name = '' ) {
		if ( empty( $file_name ) ) {
			$file_name = $this->plugin_slug . '-system-info.txt';
		}

		nocache_headers();

		header( 'Content-type: text/plain' );
		header( 'Content-Disposition: attachment; filename="' . $file_name . '"' );

		echo wp_strip_all_tags( $_POST[ $this->plugin_slug . '-system-info'] );
		die();
	}

	/**
	 * Get current theme name.
	 *
	 * @return string Current theme name.
	 */
	protected function get_current_theme_name() {
		if ( get_bloginfo( 'version' ) < '3.4' ) {
			$theme_data = get_theme_data( get_stylesheet_directory() . '/style.css' );

			return $theme_data['Name'] . ' ' . $theme_data['Version'];
		}

		$theme_data = wp_get_theme();

		return $theme_data->Name . ' ' . $theme_data->Version;
	}

	/**
	 * Try to identity the hosting provider.
	 *
	 * @return string Web host name if identified, empty string otherwise.
	 */
	protected function identify_host() {
		$host = '';

		if ( defined( 'WPE_APIKEY' ) ) {
			$host = 'WP Engine';
		} elseif ( defined( 'PAGELYBIN' ) ) {
			$host = 'Pagely';
		}

		/**
		 * Filter the identified webhost.
		 *
		 * @param string $host Identified web host.
		 * @param string $plugin_name Plugin slug.
		 */
		return apply_filters( 'system_info_host', $host, $this->plugin_slug );
	}

	/**
	 * Print plugins that are currently active.
	 */
	protected function print_current_plugins() {
		$plugins        = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( $plugins as $plugin_path => $plugin ) {
			// If the plugin isn't active, don't show it.
			if ( ! in_array( $plugin_path, $active_plugins, true ) ) {
				continue;
			}

			echo $plugin['Name'] . ': ' . $plugin['Version'] . "\n";
		}
	}

	/**
	 * Print network active plugins.
	 */
	protected function print_network_active_plugins() {
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
}
