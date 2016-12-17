<?php namespace EmailLog\Core\UI\Component;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Create Addon list UI.
 *
 * Retrieve and render the Addons list.
 *
 * @since   2.0
 * @package EmailLog\Core\UI
 */
class AddonController {

	/**
	 * @var string Plugin basename.
	 */
	protected $plugin_dir_url;

	/**
	 * @var string Error message.
	 */
	protected $error_message;

	/**
	 * @var const Cache expiration in hours.
	 */
	const CACHE_EXPIRY_IN_HRS = 12;

	/**
	 * Initialize the plugin.
	 */
	public function __construct( $file ) {
		$this->plugin_dir_url = plugin_dir_url( $file );
	}

	/**
	 * Retrieves and outputs the Addon list HTML.
	 *
	 * return array If API call fails, the array is empty, else contains the addons info.
	 */
	protected function get_addons() {
		$addons = array();

		// Get Addons result array from Cache if available.
		if ( false === ( $addons = get_transient( 'el_addons_adm' ) ) ) {

			// The products endpoint does not need a key or token to render published products.
			// @todo: Change the API Url to get the actual addons.
			$response = wp_remote_get( 'http://local.wordpress.dev/edd-api/products/' );

			if ( ! is_wp_error( $response ) && is_array( $response ) ) {

				$body = wp_remote_retrieve_body( $response );
				// Convert the JSON response to array
				$addons = json_decode( $body, true );

				/*
				 * Cache Addons result for performance.
				 * Transient data other than string type are automatically serialized and deserialized.
				 * @link http://wordpress.stackexchange.com/a/123031/83739
				 */
				set_transient( 'el_addons_adm', $addons, self::CACHE_EXPIRY_IN_HRS * HOUR_IN_SECONDS );
			} else {
				// Incase of error, default to empty array.
				$addons = array();
			}
		}

		// Default the array to empty when required array key don't exist.
		if ( ! array_key_exists( 'products', $addons ) ) {
			$addons = array();
		}

		return $addons;
	}

	/**
	 * Displays addons.
	 *
	 * Invokes `render_addon()` to display individual addons.
	 *
	 * return void
	 */
	public function render_addons() {
		$addons_result = $this->get_addons();

		// Checks for any errors in the API call.
		if ( empty( $addons_result ) ) {
			// @todo: Include hyperlink if necessary.
			$this->error_message =  __( 'We are not able to retrieve the add-on list now. Visit add-on page link to view the add-ons.', 'email-log' );
			$this->render_addon_error();
		} else {
			// The array key is set by the EDD plugin
			$addons = $addons_result['products'];
			foreach ( $addons as $addon ) {
				$this->render_addon( $addon );
			}
		}
	}

	/**
	 * Renders HTML of individual addon.
	 *
	 * return void
	 */
	public function render_addon( $addon ) {
		$addon_title       = $addon['info']['title'];
		$addon_thumbnail   = $addon['info']['thumbnail'];
		$addon_description = $addon['info']['excerpt'];
		$addon_buy_button  = __( 'Gear up!', 'email-log');
		?>
		<div class="el-addon">
			<h3 class="el-addon-title">
				<?php echo $addon_title; ?>
			</h3>

			<a href="#" title="<?php echo $addon_title; ?>">

				<img src="<?php echo $addon_thumbnail; ?>" class="attachment-showcase wp-post-image" alt="<?php echo $addon_title; ?>" title="<?php echo $addon_title; ?>" />
			</a>

			<p>
				<?php echo $addon_description; ?>
			</p>

			<a href="#" class="button-secondary"><?php echo $addon_buy_button; ?></a>
		</div> <!-- .el-addon -->
		<?php
	}

	/**
	 * Render error in Addon page if any.
	 *
	 * return void
	 */
	public function render_addon_error() {
		?>
		<span class="el-addon-error">
			<?php
				// Error message set in render_addons() method.
				echo $this->error_message;
			?>
		</span>
		<?php
	}

	/**
	 * Renders the HTML for the Addons page.
	 */
	public function render_page() {
		// Use Plugin version as CSS version to bust cache.
		$stylesheet_version = \EmailLog\Core\EmailLog::VERSION;

		// Enqueue the required styles
		wp_enqueue_style( 'el_addon_adm_pg', $this->plugin_dir_url . 'assets/css/admin/addon-list.css', array(), $stylesheet_version, 'all' );
	?>
		<p>
			<?php _e( 'These extensions <em><strong>add functionality</strong></em> to your existing Email logs.', 'email-log' ); ?>
		</p>
		<div class="el-container">
			<?php $this->render_addons(); ?>
			<div class="clear"></div>
		</div> <!-- .el-container -->
	<?php
	}
}
