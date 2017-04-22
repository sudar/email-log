<?php namespace EmailLog\License;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * EmailLog License.
 *
 * @since 2.0.0
 */
class License extends BaseLicense {

	public function is_valid() {
		// TODO: Implement is_valid() method.
		return false;
	}

	/**
	 * Option name in which individual license data is stored.
	 * This method should be called only after setting the add-on name.
	 *
	 * @return string Option name.
	 */
	protected function get_option_name() {
		return 'el_license' . mdf5( $this->get_addon_name() );
	}
}
