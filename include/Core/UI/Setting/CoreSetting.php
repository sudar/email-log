<?php namespace EmailLog\Core\UI\Setting;

use EmailLog\Core\UI\Page\LogListPage;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * All Email Log Core settings.
 *
 * @since 2.1.0
 */
class CoreSetting extends Setting {

	protected function initialize() {
		$this->section->id          = 'email-log-core';
		$this->section->title       = __( 'Core Email Log Settings', 'email-log' );
		$this->section->option_name = 'email-log-core';

		$this->section->field_labels = array(
			'allowed_user_roles'  => __( 'Allowed User Roles', 'email-log' ),
			'remove_on_uninstall' => __( 'Remove Data on Uninstall?', 'email-log' ),
		);

		$this->section->default_value = array(
			'allowed_user_roles'  => array(),
			'remove_on_uninstall' => '',
		);

		$this->load();
	}

	/**
	 * Override `load` method so that the core settings are displayed first.
	 *
	 * @inheritdoc
	 */
	public function load() {
		add_filter( 'el_setting_sections', array( $this, 'register' ), 9 );

		add_action( 'update_option_' . $this->section->option_name, array( $this, 'allowed_user_roles_changed'), 10, 2 );
	}

	/**
	 * Renders the Email Log `Allowed User Roles` settings.
	 *
	 * @param array $args Arguments.
	 */
	public function render_allowed_user_roles_settings( $args ) {
		$option         = $this->get_value();
		$selected_roles = $option[ $args['id'] ];

		$field_name = $this->section->option_name . '[' . $args['id'] . '][]';

		$available_roles = get_editable_roles();
		unset( $available_roles['administrator'] );
		?>

		<p>
			<input type="checkbox" checked disabled><?php _e( 'Administrator', 'email-log' ); ?>
		</p>

		<?php foreach ( $available_roles as $role_id => $role ) : ?>
			<p>
				<input type="checkbox" name="<?php echo esc_attr( $field_name ); ?>" value="<?php echo esc_attr( $role_id ); ?>"
					<?php \EmailLog\Util\checked_array( $selected_roles, $role_id ); ?>>

				<?php echo $role['name']; ?>
			</p>
		<?php endforeach; ?>

		<p>
			<em>
				<?php _e( '<strong>Note:</strong> Users with the above User Roles can view Email Logs.', 'email-log' ); ?>
				<?php _e( 'Administrator role always has access and cannot be disabled.', 'email-log' ); ?>
			</em>
		</p>

		<?php
	}

	/**
	 * Sanitize allowed user roles setting.
	 *
	 * @param array $roles User selected user roles.
	 *
	 * @return array Sanitized user roles.
	 */
	public function sanitize_allowed_user_roles( $roles ) {
		if ( ! is_array( $roles ) ) {
			return array();
		}

		return array_map( 'sanitize_text_field', $roles );
	}

	/**
	 * Renders the Email Log `Remove Data on Uninstall?` settings.
	 *
	 * @param array $args
	 */
	public function render_remove_on_uninstall_settings( $args ) {
		$option      = $this->get_value();
		$remove_data = $option[ $args['id'] ];

		$field_name = $this->section->option_name . '[' . $args['id'] . ']';
		?>

		<input type="checkbox" name="<?php echo esc_attr( $field_name ); ?>" value="true" <?php checked( 'true', $remove_data ); ?>>
		<?php _e( 'Check this box if you would like to completely remove all of its data when the plugin is deleted.', 'email-log' ) ?>

		<p>
			<em>
				<?php _e( '<strong>Note:</strong> You can also export the Email Logs using our <a href="https://wpemaillog.com/addons/export-logs/" rel="noopener noreferrer" target="_blank">Export Logs</a> add-on.', 'email-log' ); ?>
			</em>
		</p>

		<?php
	}

	/**
	 * Sanitize Remove on uninstall value.
	 *
	 * @param string $value User entered value.
	 *
	 * @return string Sanitized value.
	 */
	public function sanitize_remove_on_uninstall( $value ) {
		return sanitize_text_field( $value );
	}

	/**
	 * Change user role capabilities when the allowed user role list is changed.
	 *
	 * @param array $old_value Old Value.
	 * @param array $new_value New Value.
	 */
	public function allowed_user_roles_changed( $old_value, $new_value ) {
		$old_roles = array();
		$new_roles = array();

		if ( array_key_exists( 'allowed_user_roles', $old_value ) ) {
			$old_roles = $old_value['allowed_user_roles'];
			if ( ! is_array( $old_roles ) ) {
				$old_roles = array( $old_roles );
			}
		}

		if ( array_key_exists( 'allowed_user_roles', $new_value ) ) {
			$new_roles = $new_value['allowed_user_roles'];
			if ( ! is_array( $new_roles ) ) {
				$new_roles = array( $new_roles );
			}
		}

		foreach ( $old_roles as $old_role ) {
			$role = get_role( $old_role );

			if ( ! is_null( $role ) ) {
				$role->remove_cap( LogListPage::CAPABILITY );
			}
		}

		foreach ( $new_roles as $new_role ) {
			$role = get_role( $new_role );

			if ( ! is_null( $role ) ) {
				$role->add_cap( LogListPage::CAPABILITY );
			}
		}
	}
}
