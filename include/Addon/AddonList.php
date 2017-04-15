<?php namespace EmailLog\Addon;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Retrieve the list of add-ons and render them.
 *
 * @since 2.0.0
 */
class AddonList {

	const CACHE_EXPIRY_IN_HRS = 12;
	const CACHE_KEY = 'el_addon_list';

	/**
	 * Add-on list.
	 *
	 * @var Addon[]
	 */
	protected $addons;

	/**
	 * Store URL.
	 *
	 * @var string
	 */
	protected $store_url;

	/**
	 * Create a list of add-ons.
	 *
	 * @param Addon[]     $addons    List of Add-ons. If not passed, they will be automatically loaded.
	 * @param null|string $store_url Store url.
	 */
	public function __construct( $addons = null, $store_url = null ) {
		if ( null === $addons ) {
			$addons = $this->get_addons();
		}

		if ( null === $store_url ) {
			$email_log = email_log();
			$store_url = $email_log->get_store_url();
		}

		$this->addons = $addons;
		$this->store_url = $store_url;
	}

	/**
	 * Setup page to render the list of add-ons.
	 */
	public function render() {
		?>

		<div class="el-container">
			<?php $this->render_addons(); ?>
			<div class="clear"></div>
		</div> <!-- .el-container -->
		<?php
	}

	/**
	 * Retrieve the list of add-ons by calling the store API.
	 *
	 * @return Addon[] List of add-ons, empty array if API call fails.
	 */
	protected function get_addons() {
		if ( false === ( $addons = get_transient( self::CACHE_KEY ) ) ) {
			$response = wp_remote_get( $this->get_api_url() );

			if ( is_wp_error( $response ) || ! is_array( $response ) ) {
				// TODO: Don't keep trying if the server is down.
				return array();
			}

			$addons = $this->parse_response( wp_remote_retrieve_body( $response ) );

			if ( ! empty( $addons ) ) {
				set_transient( self::CACHE_KEY, $addons, self::CACHE_EXPIRY_IN_HRS * HOUR_IN_SECONDS );
			}
		}

		return $addons;
	}

	/**
	 * Parse the response and get the list of add-on.
	 *
	 * @param string $response API Response.
	 *
	 * @return array List of Add-ons.
	 */
	protected function parse_response( $response ) {
		$json = json_decode( $response, true );

		if ( ! is_array( $json ) || ! array_key_exists( 'products', $json ) ) {
			return array();
		}

		return $this->build_addon_list( $json['products'] );
	}

	/**
	 * Build a list of Addon objects from products data array.
	 *
	 * @param array $products Products data array.
	 *
	 * @return Addon[] List of Addons.
	 */
	protected function build_addon_list( $products ) {
		$addons = array();

		foreach ( $products as $product ) {
			$addon = new Addon( $product );
			$addons[ $addon->slug ] = $addon;
		}

		return $addons;
	}

	/**
	 * Render the add-on list or display an error if the list can't be retrieved.
	 */
	protected function render_addons() {
		if ( empty( $this->addons ) ) {
			$this->render_empty_list();
		}

		foreach ( $this->addons as $addon ) {
			$addon->render();
		}
	}

	/**
	 * Display a notice if the list of add-on can't be retrieved.
	 */
	protected function render_empty_list() {
		?>
		<span class="el-addon-empty">
			<?php
				printf(
					__( 'We are not able to retrieve the add-on list now. Please visit the <a href="%s">add-on page</a> to view the add-ons.', 'email-log' ), // @codingStandardsIgnoreLine
					'https://wpemaillog.com/addons'
				);
			?>
		</span>
		<?php
	}

	/**
	 * Get an add-on by slug.
	 *
	 * @param string $slug Add-on slug.
	 *
	 * @return bool|\EmailLog\Addon\Addon Add-on if found, False otherwise.
	 */
	public function get_addon_by_slug( $slug ) {
		if ( array_key_exists( $slug, $this->addons ) ) {
			return $this->addons[ $slug ];
		}

		return false;
	}

	/**
	 * Get API URL.
	 *
	 * @return string API URL.
	 */
	protected function get_api_url() {
		return $this->store_url . '/edd-api/products/?category=addon';
	}
}
