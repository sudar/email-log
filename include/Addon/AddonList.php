<?php namespace EmailLog\Addon;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Retrieve the list of add-ons and render them.
 *
 * @since 2.0.0
 */
class AddonList {

	const CACHE_EXPIRY_IN_HRS = 12;
	const CACHE_KEY           = 'el_addon_data';

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
	 * @param Addon[]|null $addons    List of Add-ons. If not passed, they will be automatically loaded.
	 * @param string|null  $store_url Store url.
	 */
	public function __construct( $addons = null, $store_url = null ) {
		if ( null === $store_url ) {
			$email_log = email_log();
			$store_url = $email_log->get_store_url();
		}
		$this->store_url = $store_url;

		if ( null === $addons ) {
			$addons = $this->get_addons();
		}

		$this->addons = $addons;
	}

	/**
	 * Get an add-on by name.
	 *
	 * @param string $name Add-on name.
	 *
	 * @return \EmailLog\Addon\Addon|false Add-on if found, False otherwise.
	 */
	public function get_addon_by_name( $name ) {
		if ( array_key_exists( $name, $this->addons ) ) {
			return $this->addons[ $name ];
		}

		return false;
	}

	/**
	 * Is an add-on active?
	 *
	 * @since 2.4.0
	 *
	 * @param string $name Add-on name.
	 *
	 * @return bool True if add-on is present and is active, false otherwise.
	 */
	public function is_addon_active( $name ) {
		$addon = $this->get_addon_by_name( $name );

		if ( ! $addon instanceof Addon ) {
			return false;
		}

		return $addon->is_active();
	}

	/**
	 * Is an add-on installed?
	 *
	 * @since 2.4.0
	 *
	 * @param string $name Add-on name.
	 *
	 * @return bool True if add-on is present and is installed, false otherwise.
	 */
	public function is_addon_installed( $name ) {
		$addon = $this->get_addon_by_name( $name );

		if ( ! $addon instanceof Addon ) {
			return false;
		}

		return $addon->is_installed();
	}

	/**
	 * Get all add-ons that are not active (either not installed or not activated).
	 *
	 * @return \EmailLog\Addon\Addon[] List of inactive add-ons.
	 */
	public function get_inactive_addons() {
		$inactive_addons = array();

		foreach ( $this->addons as $addon ) {
			if ( ! $addon->is_active() ) {
				$inactive_addons[] = $addon;
			}
		}

		return $inactive_addons;
	}

	/**
	 * Setup page to render the list of add-ons.
	 */
	public function render() {
		?>

		<div class="el-addon-list">
			<?php $this->render_addons(); ?>
		</div> <!-- .el-container -->
		<?php
	}

	/**
	 * Retrieve the list of add-ons by calling the store API.
	 * If the store API is down, then read the data from the local JSON file.
	 *
	 * @return Addon[] List of add-ons, empty array if API call fails.
	 */
	public function get_addons() {
		$json = get_transient( self::CACHE_KEY );
		if ( false === $json ) {
			$response = wp_remote_get( $this->get_api_url() );

			if ( is_wp_error( $response ) || ! is_array( $response ) ) {
				return [];
			}

			$json = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( ! is_array( $json ) ) {
				return array();
			}

			set_transient( self::CACHE_KEY, $json, self::CACHE_EXPIRY_IN_HRS * HOUR_IN_SECONDS );
		}

		return $this->parse_response( $json );
	}

	/**
	 * Parse the response and get the list of add-on.
	 *
	 * @param array $data JSON Data array.
	 *
	 * @return array List of Add-ons.
	 */
	protected function parse_response( $data ) {
		if ( ! array_key_exists( 'products', $data ) ) {
			return array();
		}

		return $this->build_addon_list( $data['products'] );
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
			$addon                  = new Addon( $product );
			$addons[ $addon->name ] = $addon;
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
					__( 'We are not able to retrieve the add-on list now. Please visit the <a target="_blank" rel="noopener" href="%s">add-on page</a> to view the add-ons.', 'email-log' ), // @codingStandardsIgnoreLine
					'https://wpemaillog.com/store/?utm_campaign=Upsell&utm_medium=wpadmin&utm_source=addon-grid-failed'
				);
			?>
		</span>
		<?php
	}

	/**
	 * Get API URL.
	 *
	 * @return string API URL.
	 */
	protected function get_api_url() {
		return $this->store_url . '/json/products.json';
	}
}
