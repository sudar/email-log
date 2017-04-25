<?php namespace EmailLog\Addon\License;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * EmailLog License.
 *
 * @since 2.0.0
 */
class AddonLicense extends BaseLicense {

	public function is_valid() {
		if ( ! $this->license_data instanceof \stdClass || ! isset( $this->license_data->license ) ) {
			return false;
		}

		return ( 'valid' === $this->license_data->license );
	}

	/**
	 * Get License key.
	 *
	 * @return string|null License key.
	 */
	public function get_license_key() {
		if ( empty( $this->license_data ) ) {
			return parent::get_license_key();
		}

		return $this->license_data->license_key;
	}

	/**
	 * Option name in which individual license data is stored.
	 * This method should be called only after setting the add-on name.
	 *
	 * @return string Option name.
	 */
	protected function get_option_name() {
		return 'el_license_' . md5( $this->get_addon_name() );
	}
}
