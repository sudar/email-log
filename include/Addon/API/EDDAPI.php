<?php namespace EmailLog\Addon\API;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Wrapper for EDD API
 *
 * @since 2.0.0
 */
class EDDAPI {

	protected $store_url = 'https://wpemaillog.com';

	/**
	 * Activate License.
	 *
	 * @param string $license_key License Key.
	 * @param string $addon_name  Add-on Name.
	 *
	 * @return object API Response JSON Object.
	 */
	public function activate_license( $license_key, $addon_name ) {
		$params = array(
			'edd_action' => 'activate_license',
			'license'    => $license_key,
			'item_name'  => urlencode( $addon_name ),
			'url'        => home_url(),
		);

		return $this->call_edd_api( $params );
	}

	/**
	 * Deactivate License.
	 *
	 * @param string $license_key License Key.
	 * @param string $addon_name  Add-on Name.
	 *
	 * @return object API Response JSON Object.
	 */
	public function deactivate_license( $license_key, $addon_name ) {
		$params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license_key,
			'item_name'  => urlencode( $addon_name ),
			'url'        => home_url(),
		);

		return $this->call_edd_api( $params );
	}

	/**
	 * Get version information.
	 *
	 * @param string $license_key License Key.
	 * @param string $addon_name  Add-on Name.
	 *
	 * @return object API Response JSON Object.
	 */
	public function get_version( $license_key, $addon_name ) {
		$params = array(
			'edd_action' => 'get_version',
			'license'    => $license_key,
			'item_name'  => $addon_name,
			'url'        => home_url(),
		);

		return $this->call_edd_api( $params );
	}

	/**
	 * Call the EDD API.
	 *
	 * @param array $params Parameters for request.
	 *
	 * @return object API Response in JSON.
	 * @throws \Exception If there is any error while making the request.
	 *
	 * TODO: Make the errors more user friendly and provide links to support.
	 */
	protected function call_edd_api( $params ) {
		$response = wp_remote_post( $this->store_url, array(
			'timeout' => 15,
			'body'    => $params,
		) );

		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				throw new \Exception( $response->get_error_message() );
			}

			throw new \Exception( __( 'Unknown error occurred while trying to contact Email Log store. Please try again after sometime. If the problem persists contact support.', 'email-log' ) );
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body );

		if ( empty( $data ) ) {
			throw new \Exception( __( 'Unable to parse the response Email Log store response. Please try again after sometime. If the problem persists contact support.', 'email-log' ) );
		}

		return $data;
	}
}
