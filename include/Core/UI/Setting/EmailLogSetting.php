<?php
namespace EmailLog\Core\UI\Setting;
use EmailLog\Core\UI\Setting\SettingField;

class EmailLogSetting extends Setting {

	/**
	 * Implement `initialize()` method.
	 */
	protected function initialize() {
		$this->section->id          = 'email-log';
		$this->section->title       = __( 'Email Log Settings', 'email-log' );
		$this->section->option_name = 'el_email_log';

		$this->load();
	}

	/**
	 * Implement `get_fields()` method.
	 *
	 * @return array
	 */
	public function get_fields() {
		$fields = array();

		$email_log_fields = array(
			'allowed_user_roles' => __( 'Allowed User Roles', 'email-log' ),
			'retain_email_logs'  => __( 'Retain Email Logs', 'email-log' ),
		);

		foreach ( $email_log_fields as $field_id => $label ) {
			$field           = new SettingField();
			$field->id       = $field_id;
			$field->title    = $label;
			$field->args     = array( 'id' => $field_id );
			$field->callback = array( $this, 'render_email_log_settings' );

			$fields[] = $field;
		}

		return $fields;
	}

	/**
	 * Implement `render()` method.
	 */
	public function render() {
	?>
		<p><?php _e( 'Email Log Settings lets you control who can view Email Logs and lets you keep the Email Logs when you delete the plugin.', 'email-log' ); ?></p>
	<?php
	}

	/**
	 * Implement `sanitize()` method.
	 *
	 * @param mixed $values {@inheritDoc}
	 *
	 * @return mixed $values {@inheritDoc}
	 */
	public function sanitize( $values ) {
		if ( ! is_array( $values ) ) {
			return array();
		}
		foreach ( $values as $key => $value ) {
			if ( $key === 'allowed_user_roles' ) {
				$values[ $key ] = array_map( 'sanitize_text_field', $values[ $key ] );
			} elseif ( $key === 'retain_email_logs' ) {
				$values[ $key ] = sanitize_text_field( $value );
			}
		}
		return $values;
	}

	/**
	 * Renders the Email Log settings fields.
	 *
	 * @param array $args
	 */
	public function render_email_log_settings( $args ) {
		$option          = $this->get_value();
		if ( 'allowed_user_roles' === $args['id'] ) {
			$available_roles = get_editable_roles();
			foreach( $available_roles as $role ) {
				if ( trim( $role['name'] ) === 'Administrator' ) {
					?>
					<p><input type="checkbox" name="<?php echo esc_attr( $this->section->option_name . '[' . $args['id'] . '][]' ); ?>" value="<?php echo trim( $role['name'] ); ?>" checked="checked" /> <?php echo trim( $role['name'] ); ?>
					</p>
					<?php
				} else {
					?>
					<p><input type="checkbox" name="<?php echo esc_attr( $this->section->option_name . '[' . $args['id'] . '][]' ); ?>" value="<?php echo trim( $role['name'] ); ?>" <?php $this->checked_array( $option[ $args['id'] ], trim( $role['name'] ) ); ?> /> <?php echo trim( $role['name'] ); ?>
					</p>
					<?php
				}
			}
			?>
			<p><?php _e( '<em><strong>Note:</strong> Users with the following <strong>User Roles</strong> can view Email Logs. The default User Role is \'<strong>administrator</strong>\'.', 'email-log' ); ?></p>
			<p><?php _e( 'Administrator role cannot be disabled.</em>', 'email-log' ); ?></p>
			<?php
		} elseif ( 'retain_email_logs' === $args['id'] ) {
?>
			<input type="checkbox" name="<?php echo esc_attr( $this->section->option_name . '[' . $args['id'] . ']' ); ?>" value="true" <?php checked( 'true', $option[ $args['id'] ] ); ?> /> <?php _e( 'Keep Email Log entries when you delete the Email Log plugin.', 'email-log' ) ?>
            <p><?php _e( '<em><em><strong>Note:</strong> You can access the logs again, by installing the Email Log plugin anytime.</em>', 'email-log' ); ?></p>
<?php
		}
}

	/**
	 * Checks the Checkbox when values are present in a given array.
	 *
	 * Use this function in Checkbox fields.
	 *
	 * @param array $values   List of all possible values.
	 * @param string $current The current value to be checked.
	 */
	public function checked_array( $values, $current ) {
		if ( in_array( $current, $values ) ) {
			echo "checked='checked'";
		}
	}
}