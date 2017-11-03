<?php namespace EmailLog\Core\UI\Setting;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * A Section used in Email Log Settings page.
 * Ideally each add-on may have a different setting section.
 *
 * @see add_settings_section()
 * @since 2.0.0
 */
class SettingSection {

	public $id;
	public $title;
	public $callback;

	/**
	 * Each section will have a single option name and the value will be array.
	 * All individual fields will be stored as an element in the value array.
	 *
	 * @var string
	 */
	public $option_name;

	/**
	 * Sanitize callback for the setting.
	 * An array will be passed to this callback.
	 *
	 * @var callable
	 */
	public $sanitize_callback;

	/**
	 * List of fields for this section.
	 *
	 * @var SettingField[]
	 */
	public $fields = array();

	/**
	 * Default value of the fields.
	 *
	 * @var array
	 *
	 * @since 2.1.0
	 */
	public $default_value = array();

	/**
	 * Field labels.
	 *
	 * @var array
	 *
	 * @since 2.1.0
	 */
	public $field_labels = array();

	/**
	 * Add a field to the section.
	 *
	 * @param \EmailLog\Core\UI\Setting\SettingField $field Field to add.
	 */
	public function add_field( SettingField $field ) {
		$this->fields[] = $field;
	}
}
