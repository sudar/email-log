<?php
namespace EmailLog\Core\UI\Setting;
use EmailLog\Core\UI\Setting\SettingField;

class EmailLogSetting extends Setting {

	/**
	 * Implement `initialize()` method.
	 */
	protected function initialize() {
		$this->section->id          = 'email-log';
		$this->section->title       = __( 'Plugin Settings', 'email-log' );
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
			'capability'  => __( 'Capability', 'email-log' ),
		);

		foreach ( $email_log_fields as $field_id => $label ) {
			$field           = new SettingField();
			$field->id       = $field_id;
			$field->title    = $label;
			$field->args     = array( 'id' => $field_id );
			$field->callback = array( $this, 'render_email_log_capability_field' );

			$fields[] = $field;
		}

		return $fields;
	}

	/**
	 * Implement `render()` method.
	 */
	public function render() {
	?>
		<p><?php _e( 'Users with the following capability can view Email Logs. The default capability is \'<strong>manage_options</strong>\'.', 'email-log' ); ?></p>
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
			$values[ $key ] = sanitize_text_field( $value );
		}

		return $values;
	}

	/**
	 * Renders the Capability field to set Capability.
	 *
	 * @param array $args
	 */
	public function render_email_log_capability_field( $args ) {
		$option         = $this->get_value();
		$admin_role_set = get_role( 'administrator' )->capabilities;
		$el_capability  = '';
	?>
		<select name="<?php echo esc_attr( $this->section->option_name . '[' . $args['id'] . ']' ); ?>">
	<?php
		foreach( $admin_role_set as $capability => $grant ) {
			if ( ( false === $option && 'manage_options' === $capability ) ||
				 ( $option[ $args['id'] ] === $capability ) ) {
				$selected      = 'selected="selected"';
				$el_capability = $capability;
			}
	?>
			<option value="<?php echo $capability; ?>" <?php echo isset( $selected ) ? $selected : ''; ?>><?php echo $capability; ?></option>
	<?php
			unset( $selected );
			}
	?>
		</select>
	<?php
		if ( isset( $el_capability ) && ! empty( $el_capability ) ) {
			$this->modify_view_log_capability( $el_capability );
		}
	}

	/**
	 * Sets the capability to view the Email Log content.
	 *
	 * Uses the Email Log API to set capability. Refer
	 * @link https://wpemaillog.com/docs/developer-docs/el_view_email_log_capability/
	 *
	 * @param string $capability
	 */
	protected function modify_view_log_capability( $capability ) {
		apply_filters( 'el_view_email_log_capability', $capability );
	}
}