<?php namespace EmailLog\Addon\License;

use EmailLog\Addon\API\EDDAPI;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Base class for for Bundle License and Add-on License.
 *
 * @since 2.0.0
 */
abstract class BaseLicense {

	protected $addon_name;
	protected $license_key;
	protected $license_data;

	/**
	 * EDD API Wrapper.
	 *
	 * @var \EmailLog\Addon\API\EDDAPI
	 */
	protected $edd_api;

	/**
	 * Get the option name in which license data will be stored.
	 *
	 * @return string Option name.
	 */
	abstract protected function get_option_name();

	/**
	 * Construct a new License object.
	 * If the API Wrapper is not provided, then a new one is initialized.
	 *
	 * @param \EmailLog\Addon\API\EDDAPI|null $edd_api (Optional) EDD API Wrapper instance. Default is null.
	 */
	public function __construct( $edd_api = null ) {
		if ( is_null( $edd_api ) ) {
			$edd_api = new EDDAPI();
		}

		$this->edd_api = $edd_api;
	}

	/**
	 * Set the license Key.
	 *
	 * @param string $license_key License Key.
	 */
	public function set_license_key( $license_key ) {
		$this->license_key = $license_key;
	}

	/**
	 * Get the license key.
	 *
	 * @return string|null License Key.
	 */
	public function get_license_key() {
		return $this->license_key;
	}

	/**
	 * Set add-on name.
	 *
	 * @param string $addon_name Add-on Name.
	 */
	public function set_addon_name( $addon_name ) {
		$this->addon_name = $addon_name;
	}

	/**
	 * Get the add-on name.
	 *
	 * @return string Add-on name.
	 */
	public function get_addon_name() {
		return $this->addon_name;
	}

	/**
	 * Get the expiry date of the license.
	 *
	 * @return false|string Expiry date in `yyyy-mm-dd hh:mm:ss` format if license is valid, False otherwise.
	 */
	public function get_expiry_date() {
		if ( ! empty( $this->license_data ) && isset( $this->license_data->expires ) ) {
			return $this->license_data->expires;
		}

		return false;
	}

	/**
	 * Has the bundle license expired?
	 *
	 * @since 2.3.0
	 *
	 * @return bool True if expired, False otherwise.
	 */
	public function has_expired() {
		$is_valid = $this->is_valid();

		if ( ! $is_valid ) {
			return true;
		}

		$expiry_date = $this->get_expiry_date();

		if ( ! $expiry_date ) {
			return true;
		}

		return ( strtotime( $expiry_date ) < time() );
	}

	/**
	 * Is the license activated and valid?
	 *
	 * @since 2.3.0 Moved to BaseLicense class.
	 *
	 * @return bool True if license is active, False otherwise.
	 */
	public function is_valid() {
		if ( ! $this->license_data instanceof \stdClass || ! isset( $this->license_data->license ) ) {
			return false;
		}

		return ( 'valid' === $this->license_data->license );
	}

	/**
	 * Get the renewal link for bundle license.
	 *
	 * @since 2.3.0
	 *
	 * @return string Renewal link.
	 */
	public function get_renewal_link() {
		$license_key = $this->get_license_key();

		if ( empty( $license_key ) ) {
			return 'https://wpemaillog.com/store/?utm_campaign=Renewal&utm_medium=wpadmin&utm_source=renewal-notice';
		}

		return sprintf( 'https://wpemaillog.com/checkout/?edd_license_key=%s&utm_campaign=Renewal&utm_medium=wpadmin&utm_source=renewal-notice', $license_key );
	}

	/**
	 * Activate License by calling EDD API.
	 * The license data returned by API is stored in an option.
	 *
	 * @throws \Exception In case of communication errors or License Issues.
	 *
	 * @return object API Response JSON Object.
	 */
	public function activate() {
		$response = $this->edd_api->activate_license( $this->get_license_key(), $this->get_addon_name() );

		if ( $response->success && 'valid' === $response->license ) {
			$response->license_key = $this->get_license_key();

			$this->store( $response );

			return $response;
		}

		switch ( $response->error ) {
			case 'expired':
				$message = sprintf(
					/* translators: 1 License expiry date, 2 License Renewal link */
					__( 'Your license key expired on %1$s. Please <a href="%2$s">renew it</a> to receive automatic updates and support.', 'email-log' ),
					date_i18n( get_option( 'date_format' ), strtotime( $response->expires, current_time( 'timestamp' ) ) ),
					'https://wpemaillog.com/checkout/?edd_license_key=' . $this->get_license_key() . '&utm_campaign=Renewal&utm_medium=wpadmin&utm_source=activation-failed'
				);
				break;

			case 'revoked':
				$message = __( 'Your license key has been disabled.', 'email-log' );
				break;

			case 'missing':
				$message = __( 'Your license key is invalid.', 'email-log' );
				break;

			case 'invalid':
			case 'site_inactive':
				$message = __( 'Your license is not active for this URL.', 'email-log' );
				break;

			case 'item_name_mismatch':
				/* translators: 1 Add-on name */
				$message = sprintf( __( 'Your license key is not valid for %s.', 'email-log' ), $this->get_addon_name() );
				break;

			case 'no_activations_left':
				$message = sprintf(
					/* translators: 1 License Upgrade link */
					__( 'Your license key has reached its activation limit. Please <a href="%s">upgrade</a> your license.', 'email-log' ),
					'https://wpemaillog.com/account/?utm_campaign=Upgrade&utm_medium=wpadmin&utm_source=activation-failed'
				);
				break;

			default:
				$message = __( 'An error occurred, please try again.', 'email-log' );
				break;
		}

		throw new \Exception( $message );
	}

	/**
	 * Deactivate the license by calling EDD API.
	 * The stored license data will be cleared.
	 *
	 * @throws \Exception In case of communication errors.
	 *
	 * @return object API Response JSON object.
	 */
	public function deactivate() {
		$response = $this->edd_api->deactivate_license( $this->get_license_key(), $this->get_addon_name() );

		if ( $response->success && 'deactivated' === $response->license ) {
			$this->clear();

			return $response;
		}

		if ( $response->expires < time() ) {
			// license has expired. Expired license can't be de-activated. So let's just clear it.
			$this->clear();

			return $response;
		}

		$message = __( 'An error occurred, please try again.', 'email-log' );

		if ( isset( $response->error ) ) {
			$message .= ' ' . $response->error;
		}

		throw new \Exception( $message );
	}

	/**
	 * Get version information by calling EDD API.
	 * // TODO: Currently not used. Will be removed eventually.
	 *
	 * @throws \Exception In case of communication errors.
	 *
	 * @return object API Response JSON Object.
	 */
	public function get_version() {
		$response = $this->edd_api->get_version( $this->get_license_key(), $this->get_addon_name() );

		if ( isset( $response->new_version ) && ! isset( $response->msg ) ) {
			return $response;
		}

		$message = __( 'An error occurred, please try again', 'email-log' ) . $response->error;

		throw new \Exception( $message );
	}

	/**
	 * Load the license data from DB option.
	 */
	public function load() {
		$this->license_data = get_option( $this->get_option_name(), null );
	}

	/**
	 * Store License data in DB option.
	 *
	 * @access protected
	 *
	 * @param object $license_data License data.
	 */
	protected function store( $license_data ) {
		$this->license_data = $license_data;
		update_option( $this->get_option_name(), $license_data );
	}

	/**
	 * Clear stored license data.
	 *
	 * @access protected
	 */
	protected function clear() {
		$this->license_data = null;
		$this->license_key  = null;
		delete_option( $this->get_option_name() );
	}
}
