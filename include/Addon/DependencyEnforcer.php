<?php namespace EmailLog\Addon;

use EmailLog\Core\Loadie;

/**
 * Enforce Addon Dependency by deactivating add-ons that don't satisfy dependency.
 *
 * @since 2.0
 */
class DependencyEnforcer implements Loadie {

	/**
	 * Addon Dependency Map.
	 * TODO: This map should be dynamically pulled from website based on base plugin version.
	 *
	 * @var array
	 */
	private $addon_dependency_map = array(
		'email-log-forward-email/email-log-forward-email.php' => '2.0',
		'email-log-more-fields/email-log-more-fields.php'     => '2.0',
		'email-log-resend-email/email-log-resend-email.php'   => '2.0',
	);

	/**
	 * Setup action and hooks.
	 */
	public function load() {
		// TODO: Ideally, this should not be called on all admin pages.
		add_action( 'admin_notices', array( $this, 'render_compatibility_notice' ) );
	}

	/**
	 * Render compatibility notice, if needed.
	 * TODO: Include link to the add-on store in the admin notice.
	 */
	public function render_compatibility_notice() {
		$deactivated_addons = $this->deactivate_outdated_active_addons();

		if ( empty( $deactivated_addons ) ) {
			return;
		}

		?>
		<div class="error">
			<p>
				<?php _e( 'The following add-ons are not compatible with the installed version of Email Log and have been deactivated.', 'email-log' ); ?>
				<ul>
					<?php
					array_walk( $deactivated_addons, function( $addon ) {
						echo '<li>' . esc_html( $addon ) . '</li>';
					} );
					?>
				</ul>
				<?php _e( 'Please get the latest version of these add-ons from add-on store.', 'email-log' ); ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Deactivate outdated active add-ons.
	 *
	 * @access private
	 *
	 * @return array List of add-ons (name and version) that got deactivated.
	 */
	private function deactivate_outdated_active_addons() {
		$deactivated_active_addons = array();

		$outdated_active_addons = $this->get_outdated_active_addons();
		foreach ( $outdated_active_addons as $addon_file_name => $outdated_active_addon ) {
			deactivate_plugins( plugin_basename( $addon_file_name ) );
			$deactivated_active_addons[] = $outdated_active_addon['Name'] . ' ' . $outdated_active_addon['Version'];
		}

		return $deactivated_active_addons;
	}

	/**
	 * Get the list of add-ons that are outdated and are active.
	 *
	 * @access private
	 *
	 * @return array List of outdated and active add-ons.
	 */
	private function get_outdated_active_addons() {
		$outdated_active_addons = array();
		$plugins                = get_plugins();

		foreach ( $this->addon_dependency_map as $addon => $required_version ) {
			if ( is_plugin_active( $addon ) ) {
				$active_addon = $plugins[ $addon ];

				if ( version_compare( $active_addon['Version'], $required_version, '<' ) ) {
					$outdated_active_addons[ $addon ] = $active_addon;
				}
			}
		}

		return $outdated_active_addons;
	}
}
