<?php namespace EmailLog\License;

use EmailLog\Core\EmailLog;
use EmailLog\Core\Loadie;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Handles the add-on licensing for Email Log.
 *
 * There can be one normal license for each add-on or one bundle license for all add-ons.
 * This class is final because we don't want other plugins to interfere with Email Log licensing.
 *
 * @since 2.0.0
 */
final class Licenser implements Loadie {

	private $bundle_license;

	/**
	 * Licenser constructor.
	 * If the bundle_license object is not passed a new object is created.
	 *
	 * @param \EmailLog\License\BundleLicense $bundle_license Optional. Bundle License.
	 */
	public function __construct( $bundle_license = null ) {
		if ( ! $bundle_license instanceof BundleLicense ) {
			$bundle_license = new BundleLicense();
		}

		$this->bundle_license = $bundle_license;
	}

	/**
	 * Load all Licenser related hooks.
	 *
	 * @inheritdoc
	 */
	public function load() {
		$this->bundle_license->load();

		add_action( 'el_before_addon_list', array( $this, 'render_bundle_license_form' ) );

		add_action( 'el_bundle_license_activate', array( $this, 'activate_bundle_license' ) );
		add_action( 'el_bundle_license_deactivate', array( $this, 'deactivate_bundle_license' ) );
	}

	/**
	 * Render the Bundle License Form.
	 */
	public function render_bundle_license_form() {
		$action = 'el_bundle_license_activate';
		$action_text = __( 'Activate', 'email-log' );
		$button_class = 'button-primary';

		if ( $this->bundle_license->is_active() ) {
			$action = 'el_bundle_license_deactivate';
			$action_text = __( 'Deactivate', 'email-log' );
			$button_class = '';
		}
		?>

		<div class="bundle-license">
			<?php if ( ! $this->bundle_license->is_active() ) : ?>
				<p class="notice notice-warning">
					<?php
						printf(
							__( "Enter your license key to activate add-ons. If you don't have a license, then you can <a href='%s' target='_blank'>buy it</a>", 'email-log' ),
							'https://wpemaillog.com'
						);
					?>
				</p>
			<?php endif; ?>

			<form method="post">
				<input type="text" name="el-license" class="el-license" size="40"
				       title="<?php _e( 'Email Log Bundle License Key', 'email-log' ); ?>"
				       placeholder="<?php _e( 'Email Log Bundle License Key', 'email-log' ); ?>"
					   value="<?php echo esc_attr( $this->bundle_license->get_license_key() ); ?>">

				<input type="submit" class="button button-large <?php echo sanitize_html_class( $button_class ); ?>"
					   value="<?php echo esc_attr( $action_text ); ?>">

				<input type="hidden" name="el-action" value="<?php echo esc_attr( $action ); ?>">

				<?php wp_nonce_field( $action, $action . '_nonce' ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Activate Bundle License.
	 *
	 * @param array $request Request Object.
	 */
	public function activate_bundle_license( $request ) {
		$license_key = sanitize_text_field( $request['el-license'] );

		$this->bundle_license->set_license_key( $license_key );

		try {
			$this->bundle_license->activate();
			$message = __( 'License successfully activated', 'email-log' );
			$type = 'updated';
		} catch ( \Exception $e ) {
			$message = $e->getMessage();
			$type = 'error';
		}

		add_settings_error( 'bundle-license', 'bundle-license', $message, $type );
	}

	/**
	 * Deactivate Bundle License.
	 */
	public function deactivate_bundle_license() {
		try {
			$this->bundle_license->deactivate();
			$message = __( 'License successfully deactivated', 'email-log' );
			$type = 'updated';
		} catch ( \Exception $e ) {
			$message = $e->getMessage();
			$type = 'error';
		}

		add_settings_error( 'bundle-license', 'bundle-license', $message, $type );
	}
}
