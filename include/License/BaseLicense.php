<?php namespace EmailLog\License;

use EmailLog\Core\EmailLog;
use EmailLog\Util\EDDAPI;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Base class for for Bundle License and Add-on License.
 *
 * @since 2.0.0
 */
abstract class BaseLicense {

	protected $addon_name;
	protected $license_key;

	/**
	 * EDD API Wrapper.
	 *
	 * @var \EmailLog\Util\EDDAPI
	 */
	protected $edd_api;

	/**
	 * Is the license activated and valid?
	 *
	 * @return bool True if license is active, False otherwise.
	 */
	abstract public function is_active();

	/**
	 * Get the license key.
	 *
	 * @return string License Key.
	 */
	abstract public function get_license_key();

	/**
	 * Construct a new License object.
	 * If the API Wrapper is not provided, then a new one is initialized.
	 *
	 * @param \EmailLog\Util\EDDAPI $edd_api (Optional) EDD API Wrapper instance. Default is null.
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
	 * Activate License by calling EDD API.
	 *
	 * @return object API Response JSON Object.
	 * @throws \Exception In case of communication errors or License Issues.
	 */
	public function activate() {
		$response = $this->edd_api->activate_license( $this->get_license_key(), $this->addon_name );

		if ( $response->success && 'valid' === $response->license ) {
			return $response;
		}

		switch ( $response->error ) {
			case 'expired' :
				$message = sprintf(
					__( 'Your license key expired on %s.' ),
					date_i18n( get_option( 'date_format' ), strtotime( $response->expires, current_time( 'timestamp' ) ) )
				);
				break;

			case 'revoked' :
				$message = __( 'Your license key has been disabled.' );
				break;

			case 'missing' :
				$message = __( 'Your license key is invalid.' );
				break;

			case 'invalid' :
			case 'site_inactive' :
				$message = __( 'Your license is not active for this URL.' );
				break;

			case 'item_name_mismatch' :
				$message = sprintf( __( 'Your license key is not valid for %s.' ), $this->addon_name );
				break;

			case 'no_activations_left':
				$message = __( 'Your license key has reached its activation limit.' );
				break;

			default :
				$message = __( 'An error occurred, please try again.' );
				break;
		}

		throw new \Exception( $message );
	}

	/**
	 * Deactivate the license by calling EDD API.
	 *
	 * @return object API Response JSON object.
	 * @throws \Exception In case of communication errors.
	 */
	public function deactivate() {
		$response = $this->edd_api->deactivate_license( $this->get_license_key(), $this->addon_name );

		if ( $response->success && 'deactivated' === $response->license ) {
			return $response;
		}

		switch ( $response->error ) {
			default:
				$message = __( 'An error occurred, please try again', 'email-log' ) . $response->error;
				break;
		}

		throw new \Exception( $message );
	}
}
