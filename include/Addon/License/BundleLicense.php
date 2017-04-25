<?php namespace EmailLog\Addon\License;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * BundleLicense Object.
 * There can be only one BundleLicence for all the add-ons.
 *
 * @since 2.0.0
 */
class BundleLicense extends BaseLicense {

	/**
	 * For Bundle the add-on name is hardcoded.
	 *
	 * @var string Add-on name.
	 */
	protected $addon_name = 'Email Log Bundle';

	/**
	 * Is the license valid?
	 *
	 * @return bool True if valid, False otherwise.
	 */
	public function is_valid() {
		if ( empty( $this->license_data ) ) {
			return false;
		}

		return ( 'valid' === $this->license_data->license );
	}

	/**
	 * Return bundle license key.
	 *
	 * @return string Bundle License key, if found.
	 */
	public function get_license_key() {
		if ( empty( $this->license_data ) ) {
			return parent::get_license_key();
		}

		return $this->license_data->bundle_license_key;
	}

	/**
	 * The option name in which the bundle license data will be stored.
	 *
	 * @return string Option name.
	 */
	protected function get_option_name() {
		return 'el_bundle_license';
	}

	/**
	 * Get the license key of an add-on from Bundle.
	 *
	 * @param string $addon_name Add-on name.
	 *
	 * @return bool|string False if no license key is found, otherwise license key.
	 */
	public function get_addon_license_key( $addon_name ) {
		if ( empty( $this->license_data ) ) {
			return false;
		}

		if ( ! isset( $this->license_data->bundled_licenses->{$addon_name} ) ) {
			return false;
		}

		return $this->license_data->bundled_licenses->{$addon_name}->license_key;
	}
}
