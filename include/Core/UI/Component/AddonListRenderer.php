<?php namespace EmailLog\Core\UI\Component;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Retrieve the list of add-ons and render them.
 *
 * @since 2.0.0
 */
class AddonListRenderer {

	const API_URL = 'https://wpemaillog.com/edd-api/products/?category=addon';
	const CACHE_EXPIRY_IN_HRS = 12;
	const CACHE_KEY = 'el_addon_list';

	/**
	 * Plugin File.
	 *
	 * @var string
	 */
	private $plugin_file;

	/**
	 * Create a new instance with the passed in plugin file.
	 *
	 * @param string $plugin_file Plugin File.
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
	}

	/**
	 * Setup page to render the list of add-ons.
	 */
	public function render() {
		$email_log = email_log();

		wp_enqueue_style( 'el_addon_list', plugins_url( 'assets/css/admin/addon-list.css', $this->plugin_file ), array(), $email_log->get_version() );
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
	 * @return array List of add-ons, empty array if API call fails.
	 */
	protected function get_addons() {
		if ( false === ( $addons = get_transient( self::CACHE_KEY ) ) ) {
			$response = wp_remote_get( self::API_URL );

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

		return $json['products'];
	}

	/**
	 * Render the add-on list or display an error if the list can't be retrieved.
	 */
	protected function render_addons() {
		$addons = $this->get_addons();

		if ( empty( $addons ) ) {
			$this->render_empty_list();
		}

		foreach ( $addons as $addon ) {
			$this->render_addon( $addon );
		}
	}

	/**
	 * Renders an individual addon.
	 *
	 * @param array $addon Details about an add-on.
	 */
	protected function render_addon( $addon ) {
		$addon_title       = $addon['info']['title'];
		$addon_thumbnail   = $addon['info']['thumbnail'];
		$addon_description = $addon['info']['excerpt'];
		$addon_link        = $addon['info']['permalink'];
		?>
		<div class="el-addon">
			<h3 class="el-addon-title">
				<?php echo esc_html( $addon_title ); ?>
			</h3>

			<a href="<?php echo esc_url( $addon_link ); ?>" title="<?php echo esc_attr( $addon_title ); ?>">
				<img src="<?php echo esc_url( $addon_thumbnail ); ?>" class="attachment-showcase wp-post-image"
					 alt="<?php echo esc_attr( $addon_title ); ?>" title="<?php echo esc_attr( $addon_title ); ?>">
			</a>

			<p>
				<?php echo esc_html( $addon_description ); ?>
			</p>

			<a href="#" class="button-secondary"><?php _e( 'Install Now', 'email-log' ); ?></a>
		</div> <!-- .el-addon -->
		<?php
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
}
