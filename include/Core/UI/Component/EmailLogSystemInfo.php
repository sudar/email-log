<?php

namespace EmailLog\Core\UI\Component;

use EmailLog\Core\DB\TableManager;

/**
 * Email Log System Info.
 *
 * @see \EmailLog\Core\UI\Component\SystemInfo
 * @since 2.3.0
 */
class EmailLogSystemInfo extends SystemInfo {

	/**
	 * Setup hooks and filters.
	 */
	public function load() {
		add_action( 'system_info_before', array( $this, 'print_email_log_config' ), 10, 2 );
	}

	/**
	 * ##RefactorMe
	 * Dummy method which should return license key or keys based on license type.
	 *
	 * @return string
	 */
	public function get_license_key(){
		return '';
	}

	public function print_email_log_config() {
		$email_log = email_log();
		?>
-- Email Log Configuration

Email Log Version:                  <?php echo $this->get_plugin_version() . "\n"; ?>
Number of Logs:                     <?php echo $email_log->table_manager->get_logs_count() . "\n"; ?>
Email Log DB Version:               <?php echo get_option( TableManager::DB_OPTION_NAME ) . "\n"; ?>
<?php if ( $this->get_license_key() === '' ) : ?>
License Key:                        <?php echo $this->get_license_key() . "\n"; ?>
<?php endif; ?>
<?php $email_log_core = get_option('email-log-core'); ?>
<?php if ( $email_log_core ) : ?>
Allowed Roles for Email Log View:   <?php echo implode( ', ', $email_log_core['allowed_user_roles'] ) . "\n"; ?>
Remove All Data on Uninstallation:  <?php echo $email_log_core['remove_on_uninstall'] !== '' ? 'Yes' : 'No' . "\n"; ?>
Disable Dash Board Widget:          <?php echo $email_log_core['hide_dashboard_widget'] === 'true' ? 'Yes' : 'No' . "\n"; ?>
<?php endif; ?>
<?php
	}

	protected function get_default_config() {
		$config = parent::get_default_config();

		$config['show_posts']      = false;
		$config['show_taxonomies'] = false;

		return $config;
	}

	protected function get_plugin_version() {
		$plugin_path = WP_PLUGIN_DIR . '/email-log/email-log.php';
		$plugin_data = get_plugin_data( $plugin_path );

		return $plugin_data['Version'];
	}
}
