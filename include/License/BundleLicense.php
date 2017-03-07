<?php namespace EmailLog\License;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * BundleLicense Object.
 * There can be only one BundleLicence for all the add-ons.
 *
 * @since 2.0.0
 */
class BundleLicense extends BaseLicense {

	protected $addon_name = 'Email Log Bundle';
	protected $option_name = 'el_bundle_license';
	protected $data;

	/**
	 * Activate the license and store the response.
	 *
	 * @inheritdoc
	 */
	public function activate() {
		$response = parent::activate();
		$this->store( $response );
	}

	/**
	 * Deactivate the license and clear the license data from options.
	 *
	 * @inheritdoc
	 */
	public function deactivate() {
		parent::deactivate();
		$this->clear();
	}

	public function is_active() {
		if ( empty( $this->data ) ) {
			return false;
		}

		if ( 'valid' === $this->data->license ) {
			return true;
		}

		return false;
	}

	public function get_license_key() {
		if ( empty( $this->data ) ) {
			return $this->license_key;
		}

		return $this->data->bundle_license_key;
	}

	/**
	 * Load the license data from DB option.
	 */
	public function load() {
		$this->data = get_option( $this->option_name, null );
	}

	/**
	 * Store License data in DB option.
	 *
	 * @access protected
	 *
	 * @param object $data License data.
	 */
	protected function store( $data ) {
		$this->data = $data;
		update_option( $this->option_name, $data );
	}

	/**
	 * Clear stored license data.
	 *
	 * @access protected
	 */
	protected function clear() {
		unset( $this->data );
		delete_option( $this->option_name );
	}
}
