<?php namespace EmailLog\Core\UI\Page;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Settings Page.
 * This page is displayed only if any add-on has a setting enabled.
 *
 * @since 2.0.0
 */
class SystemInfoPage extends BasePage {
	const PAGE_SLUG = 'ststem_infos';
	/**
	 * Specify additional hooks.
	 *
	 * @inheritdoc
	 */
	public function load() {
		parent::load();

		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Register settings and add setting sections and fields.
	 */
	public function register_settings() {
		$sections = $this->get_setting_sections();

		foreach ( $sections as $section ) {
			register_setting(
				self::PAGE_SLUG,
				$section->option_name,
				array( 'sanitize_callback' => $section->sanitize_callback )
			);

			add_settings_section(
				$section->id,
				$section->title,
				$section->callback,
				self::PAGE_SLUG
			);

			foreach ( $section->fields as $field ) {
				add_settings_field(
					$section->id . '[' . $field->id . ']',
					$field->title,
					$field->callback,
					self::PAGE_SLUG,
					$section->id,
					$field->args
				);
			}
		}
	}

	/**
	 * Get a list of setting sections defined.
	 * An add-on can define a setting section.
	 *
	 * @return \EmailLog\Core\UI\Setting\SettingSection[] List of defined setting sections.
	 */
	protected function get_setting_sections() {
		/**
		 * Specify the list of setting sections in the settings page.
		 * An add-on can add its own setting section by adding an instance of
		 * SectionSection to the array.
		 *
		 * @since 2.0.0
		 *
		 * @param \EmailLog\Core\UI\Setting\SettingSection[] List of SettingSections.
		 */
		return apply_filters( 'el_setting_sections', array() );
	}

	/**
	 * Register page.
	 */
	public function register_page() {

		$sections = $this->get_setting_sections();

		if ( empty( $sections ) ) {
			return;
		}

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
	 * @since 5.5.4
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
	 * Render the page.
	 * //TODO: Convert these sections into tabs.
	 */
	public function render_page() {
		global $wpdb;
		$plugin_version = '2.2.5';
		?>
		<div class="wrap">
			<h1><?php _e( 'Email Log  - System Info', 'email-log' ); ?></h1>

		<textarea wrap="off" style="width:100%;height:500px;font-family:Menlo,Monaco,monospace;white-space:pre;" readonly="readonly" onclick="this.focus();this.select()" id="system-info-textarea" name="bulk-delete-sysinfo" title="<?php _e( 'To copy the system info, click below then press Ctrl + C (PC) or Cmd + C (Mac).', 'bulk-delete' ); ?>">
### Begin System Info ###

Multisite:                <?php echo is_multisite() ? 'Yes' . "\n" : 'No' . "\n" ?>

SITE_URL:                 <?php echo site_url() . "\n"; ?>
HOME_URL:                 <?php echo home_url() . "\n"; ?>
Browser:                  <?php echo esc_html( $_SERVER['HTTP_USER_AGENT'] ), "\n"; ?>

Permalink Structure:      <?php echo get_option( 'permalink_structure' ) . "\n"; ?>
Active Theme:             <?php echo $this->el_get_current_theme_name() . "\n"; ?>
<?php
		$host = bd_identify_host();
		if ( '' !== $host ) : ?>
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

Email log Version:      <?php echo $plugin_version . "\n"; ?>
WordPress Version:        <?php echo get_bloginfo( 'version' ) . "\n"; ?>
PHP Version:              <?php echo PHP_VERSION . "\n"; ?>
MySQL Version:            <?php echo $wpdb->db_version() . "\n"; ?>
Web Server Info:          <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>

WordPress Memory Limit:   <?php echo WP_MEMORY_LIMIT; ?><?php echo "\n"; ?>
WordPress Max Limit:      <?php echo WP_MAX_MEMORY_LIMIT; ?><?php echo "\n"; ?>
PHP Memory Limit:         <?php echo ini_get( 'memory_limit' ) . "\n"; ?>

SAVEQUERIES:              <?php echo defined( 'SAVEQUERIES' ) ? SAVEQUERIES ? 'Enabled' . "\n" : 'Disabled' . "\n" : 'Not set' . "\n" ?>
WP_DEBUG:                 <?php echo defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' . "\n" : 'Disabled' . "\n" : 'Not set' . "\n" ?>
WP_SCRIPT_DEBUG:          <?php echo defined( 'WP_SCRIPT_DEBUG' ) ? WP_SCRIPT_DEBUG ? 'Enabled' . "\n" : 'Disabled' . "\n" : 'Not set' . "\n" ?>

GMT Offset:               <?php echo esc_html( get_option( 'gmt_offset' ) ), "\n\n"; ?>
DISABLE_WP_CRON:          <?php echo defined( 'DISABLE_WP_CRON' ) ? DISABLE_WP_CRON ? 'Yes' . "\n" : 'No' . "\n" : 'Not set' . "\n" ?>
WP_CRON_LOCK_TIMEOUT:     <?php echo defined( 'WP_CRON_LOCK_TIMEOUT' ) ? WP_CRON_LOCK_TIMEOUT : 'Not set', "\n" ?>
EMPTY_TRASH_DAYS:         <?php echo defined( 'EMPTY_TRASH_DAYS' ) ? EMPTY_TRASH_DAYS : 'Not set', "\n" ?>

PHP Safe Mode:            <?php echo ini_get( 'safe_mode' ) ? 'Yes' : 'No', "\n"; // phpcs:ignore PHPCompatibility.PHP.DeprecatedIniDirectives.safe_modeDeprecatedRemoved?>
PHP Upload Max Size:      <?php echo ini_get( 'upload_max_filesize' ) . "\n"; ?>
PHP Post Max Size:        <?php echo ini_get( 'post_max_size' ) . "\n"; ?>
PHP Upload Max Filesize:  <?php echo ini_get( 'upload_max_filesize' ) . "\n"; ?>
PHP Time Limit:           <?php echo ini_get( 'max_execution_time' ) . "\n"; ?>
PHP Max Input Vars:       <?php echo ini_get( 'max_input_vars' ) . "\n"; // phpcs:ignore PHPCompatibility.PHP.NewIniDirectives.max_input_varsFound?>
PHP Arg Separator:        <?php echo ini_get( 'arg_separator.output' ) . "\n"; ?>
PHP Allow URL File Open:  <?php echo ini_get( 'allow_url_fopen' ) ? 'Yes' : 'No', "\n"; ?>

WP Table Prefix:          <?php echo $wpdb->prefix, "\n";?>

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

<?php bd_print_current_plugins(); ?>

<?php
		if ( is_multisite() ) : ?>
NETWORK ACTIVE PLUGINS:

<?php
			bd_print_network_active_plugins();
		endif;
?>

<?php do_action( 'bd_system_info_after' );?>
### End System Info ###</textarea>

		<p class="submit">
			<input type="hidden" name="bd_action" value="download_sysinfo">
			<?php submit_button( 'Download System Info File', 'primary', 'bulk-delete-download-sysinfo', false ); ?>
		</p>


		</div>
		<?php

		$this->render_page_footer();
	}
}