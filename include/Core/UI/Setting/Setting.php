<?php namespace EmailLog\Core\UI\Setting;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Email Log Setting.
 * Contains a setting section and a number of fields.
 *
 * @since 2.0.0
 */
abstract class Setting {

	/**
	 * @var \EmailLog\Core\UI\Setting\SettingSection
	 */
	protected $section;

	/**
	 * Set default values for SettingSection.
	 * Further customization can be done by the add-on in the `initialize` method.
	 */
	public function __construct() {
		$this->section = new SettingSection();

		$this->initialize();

		$this->section->fields            = $this->get_fields();
		$this->section->callback          = array( $this, 'render' );
		$this->section->sanitize_callback = array( $this, 'sanitize' );
	}

	/**
	 * Setup hooks and filters.
	 */
	public function load() {
		add_filter( 'el_setting_sections', array( $this, 'register' ) );
	}

	/**
	 * Register the setting using the filter.
	 *
	 * @param SettingSection[] $sections List of existing SettingSections.
	 *
	 * @return SettingSection[] Modified list of SettingSections.
	 */
	public function register( $sections ) {
		$sections[] = $this->section;

		return $sections;
	}

	/**
	 * Get the value stored in the option.
	 * If no values are found then the default values are returned.
	 *
	 * @return array Stored value.
	 */
	public function get_value() {
		$value = get_option( $this->section->option_name );

		return wp_parse_args( $value, $this->section->default_value );
	}

	/**
	 * Customize the SettingSection.
	 *
	 * @return void
	 */
	abstract protected function initialize();

	/**
	 * Get the list of SettingFields.
	 *
	 * @return SettingField[] List of fields for the Setting.
	 */
	protected function get_fields() {
		return $this->build_fields();
	}

	/**
	 * Render the Settings section.
	 *
	 * By default it does nothing.
	 */
	public function render() {
		return;
	}

	/**
	 * Sanitize the option values.
	 *
	 * @param mixed $values User entered values.
	 *
	 * @return mixed Sanitized values.
	 */
	public function sanitize( $values ) {
		if ( ! is_array( $values ) ) {
			return array();
		}

		$values           = wp_parse_args( $values, $this->section->default_value );
		$sanitized_values = array();

		foreach ( $this->section->field_labels as $field_id => $label ) {
			$callback = array( $this, 'sanitize_' . $field_id );

			if ( is_callable( $callback ) ) {
				$sanitized_values[ $field_id ] = call_user_func( $callback, $values[ $field_id ] );
			} else {
				$sanitized_values[ $field_id ] = $values[ $field_id ];
			}
		}

		return $sanitized_values;
	}

	/**
	 * Build SettingField objects from field id and labels.
	 *
	 * @since 2.1.0
	 *
	 * @return \EmailLog\Core\UI\Setting\SettingField[] Built SettingFields.
	 */
	protected function build_fields() {
		$fields = array();

		foreach ( $this->section->field_labels as $field_id => $label ) {
			$field           = new SettingField();
			$field->id       = $field_id;
			$field->title    = $label;
			$field->args     = array( 'id' => $field_id );
			$field->callback = array( $this, 'render_' . $field_id . '_settings' );

			$fields[] = $field;
		}

		return $fields;
	}
}
