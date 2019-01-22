<?php

namespace EmailLog\Core\UI\Component;

use EmailLog\Core\DB\TableManager;
use EmailLog\Core\EmailLog;
use Sudar\WPSystemInfo\SystemInfo;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Email Log System Info.
 *
 * Uses the WPSystemInfo library.
 *
 * @since 2.3.0
 * @link https://github.com/sudar/wp-system-info
 */
class EmailLogSystemInfo extends SystemInfo {

	/**
	 * Setup hooks and filters.
	 */
	public function load() {
		add_action( 'before_system_info_for_email-log', array( $this, 'print_email_log_details' ) );
		add_action( 'before_system_info_for_email-log', array( $this, 'print_email_log_license_details' ) );
	}

	/**
	 * Print details about Email Log.
	 *
	 * PHPCS is disabled for this function since alignment will mess up the system info output.
	 * phpcs:disable
	 */
	public function print_email_log_details() {
		$email_log = email_log();

		$email_log_core = get_option( 'email-log-core' );
		?>
-- Email Log Configuration --

Email Log Version:                  <?php echo EmailLog::VERSION . "\n"; ?>
Number of Logs:                     <?php echo $email_log->table_manager->get_logs_count() . "\n"; ?>
Email Log DB Version:               <?php echo get_option( TableManager::DB_OPTION_NAME ) . "\n"; ?>
<?php if ( is_array( $email_log_core ) ) : ?>
Additional allowed user roles:      <?php echo implode( ', ', $email_log_core['allowed_user_roles'] ) . "\n"; ?>
Remove All Data on Uninstallation:  <?php echo $email_log_core['remove_on_uninstall'] !== '' ? 'Yes' : 'No' . "\n"; ?>
Disable DashBoard Widget:           <?php echo $email_log_core['hide_dashboard_widget'] === 'true' ? 'Yes' : 'No' . "\n"; ?>
<?php endif; ?>

<?php
	}
	// phpcs:enable

	/**
	 * Print details about Email Log Licenses.
	 */
	public function print_email_log_license_details() {
		$bundle_license = $this->get_bundle_license();

		if ( ! is_null( $bundle_license ) ) {
			$this->print_bundle_license_details( $bundle_license );
		} else {
			$this->print_individual_addon_license();
		}
	}

	/**
	 * Get Bundle license.
	 *
	 * @return \EmailLog\Addon\License\BundleLicense|null Bundle license or null if no bundle license.
	 */
	protected function get_bundle_license() {
		$email_log = email_log();

		$licenser       = $email_log->get_licenser();
		$bundle_license = $licenser->get_bundle_license();

		$bundle_license_key = $bundle_license->get_license_key();
		if ( ! empty( $bundle_license_key ) ) {
			return $bundle_license;
		}

		return null;
	}

	/**
	 * Print bundle license details.
	 *
	 * @param \EmailLog\Addon\License\BundleLicense $bundle_license Bundle license.
	 *
	 * PHPCS is disabled for this function since alignment will mess up the system info output.
	 * phpcs:disable
	 */
	protected function print_bundle_license_details( $bundle_license ) {
		?>
-- Email Log Bundle License --

License Key:               <?php echo $bundle_license->get_license_key(), "\n"; ?>
License Expiry Date:       <?php echo $bundle_license->get_expiry_date(), "\n"; ?>
<?php if ( $bundle_license->is_valid() ) : ?>
License Valid:             <?php echo 'Yes', "\n"; ?>
<?php else : ?>
License Valid:             <?php echo 'No', "\n"; ?>
<?php endif; ?>

<?php
	}
	// phpcs:enable

	/**
	 * Print license details of individual add-ons.
	 *
	 * PHPCS is disabled for this function since alignment will mess up the system info output.
	 * phpcs:disable
	 */
	protected function print_individual_addon_license() {
		$email_log = email_log();

		$licenser = $email_log->get_licenser();
		$addons   = $licenser->get_addon_list()->get_addons();
		?>
-- Email Log Addon License --

<?php
		foreach ( $addons as $addon ) {
			echo '- ', $addon->name;

			$license_key = $addon->get_addon_license_key();

			if ( ! empty( $license_key ) ) {
				$addon_license = $addon->get_license();
				echo ' (', $license_key, ' - ', $addon_license->get_expiry_date(), ')';
			}

			echo "\n";
		}
		echo "\n";
	}
	// phpcs:enable

	/**
	 * Change the default config.
	 *
	 * @return array Modified config.
	 */
	protected function get_default_config() {
		$config = parent::get_default_config();

		$config['show_posts']      = false;
		$config['show_taxonomies'] = false;
		$config['show_users']      = false;

		return $config;
	}
}
