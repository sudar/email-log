<?php namespace EmailLog\Addon;

use EmailLog\Addon\License\AddonLicense;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Encapsulate Add-on Data.
 *
 * @since 2.0.0
 *
 * @property-read string $name
 * @property-read string $version
 * @property-read string $thumbnail
 * @property-read string $description
 * @property-read string $link
 * @property-read string $slug
 * @property-read string $file
 * @property-read string $author
 */
class Addon {

	private $name;
	private $version;
	private $thumbnail;
	private $description;
	private $link;
	private $slug;
	private $file;
	private $author;

	protected $email_log;
	protected $license;

	/**
	 * Construct Addon object from data array.
	 *
	 * @param array                                     $data      Data array.
	 * @param \EmailLog\Addon\License\AddonLicense|null $license   Add-on License.
	 * @param \EmailLog\Core\EmailLog|null              $email_log Email Log instance.
	 */
	public function __construct( $data, $license = null, $email_log = null ) {
		$this->parse_data( $data );

		if ( null === $license ) {
			$license = new AddonLicense();
			$license->set_addon_name( $this->name );
			$license->load();
		}
		$this->license = $license;

		if ( null === $email_log ) {
			$email_log = email_log();
		}
		$this->email_log = $email_log;
	}

	/**
	 * Render the add-on in Addon list page.
	 */
	public function render() {
		?>
		<div class="el-addon">
			<h3 class="el-addon-title"> <?php echo esc_html( $this->name ); ?> </h3>

			<a rel="noopener" target="_blank" href="<?php echo esc_url( $this->link ); ?>?utm_campaign=Upsell&utm_medium=wpadmin&utm_source=addon-grid&utm_content=<?php echo $this->name; ?>"
			   title="<?php echo esc_attr( $this->name ); ?>">
				<img src="<?php echo esc_url( $this->thumbnail ); ?>" class="el-addon-image" alt="<?php echo esc_attr( $this->name ); ?>" title="<?php echo esc_attr( $this->name ); ?>">
			</a>

			<p> <?php echo esc_html( $this->description ); ?> </p>

			<?php $this->print_actions(); ?>
		</div> <!-- .el-addon -->

		<?php
	}

	/**
	 * Have a magic getter instead of having individual getters.
	 *
	 * @param string $property Property.
	 *
	 * @return string Value for the property.
	 */
	public function __get( $property ) {
		if ( isset( $this->{$property} ) ) {
			return $this->{$property};
		}

		return false;
	}

	/**
	 * Get Add-on License object.
	 *
	 * @return \EmailLog\Addon\License\AddonLicense License object.
	 */
	public function get_license() {
		return $this->license;
	}

	/**
	 * Get action links for add-ons.
	 */
	protected function print_actions() {
		if ( $this->has_valid_bundle_license() ) {
			$this->print_valid_actions();

			return;
		}

		if ( ! $this->has_valid_addon_license() ) {
			$this->print_invalid_actions();
		} else {
			$this->print_valid_actions();
		}

		$this->render_individual_license();
	}

	/**
	 * Print actions that are available when the license is valid.
	 */
	protected function print_valid_actions() {
		$actions = '';

		if ( $this->is_installed() ) {
			$actions = '<a disabled class="button button-secondary">' . _x( 'Installed', 'Installed on website but not activated', 'email-log' );

			if ( $this->is_active() ) {
				$actions .= ' &amp; ' . _x( 'Activated', 'Installed and activated on website', 'email-log' ) . '</a>';
			} else {
				$actions .= sprintf( '</a> <a class="button button-primary" href="%s">%s</a>', $this->get_activate_url(), _x( 'Activate', 'Enable addon so it may be used', 'email-log' ) );
			}
		}

		$actions .= sprintf(
			' <a class="button button-secondary" rel="noopener" target="_blank" onclick="%s" href="%s">%s</a>',
			$this->get_download_button_js(),
			$this->get_download_url(),
			_x( 'Download', 'Download to your computer', 'email-log' )
		);

		echo $actions;
	}

	/**
	 * Return the JavaScript that shows the message when the Download button is clicked.
	 *
	 * @since 2.2.4
	 *
	 * @return string JavaScript.
	 */
	protected function get_download_button_js() {
		ob_start();
		?>
		javascript:alert( "The zip file download will start now. Once the zip file is downloaded, upload it from the plugin page to install the add-on. WordPress plugin repo guidelines prevent us from automatically installing the add-on and that's why you have to do this manual step once." );
		<?php
		return ob_get_clean();
	}

	/**
	 * Print actions that are available when the license is not valid.
	 */
	protected function print_invalid_actions() {
		$label = _x( 'Activate License to Download', 'Download add-on', 'email-log' );

		if ( $this->is_installed() ) {
			$label = _x( 'Activate License to Use', 'Download and activate addon', 'email-log' );
		}

		printf(
			'<a disabled class="button-secondary disabled" title="%s" href="#">%s</a>',
			__( 'You need an active license to use the add-on', 'email-log' ),
			$label
		);
	}

	/**
	 * Render Individual license form.
	 */
	protected function render_individual_license() {
		$action         = 'el_license_activate';
		$action_text    = __( 'Activate License', 'email-log' );
		$button_class   = 'button-primary';
		$dashicon       = 'down';
		$license_wrap   = 'hidden';
		$expiry_details = '';

		if ( $this->has_valid_addon_license() ) {
			$action       = 'el_license_deactivate';
			$action_text  = __( 'Deactivate License', 'email-log' );
			$button_class = '';
			$dashicon     = 'up';
			$license_wrap = '';

			$expiry_date = date( 'F d, Y', strtotime( $this->get_license()->get_expiry_date() ) );

			if ( $this->get_license()->has_expired() ) {
				/* translators: 1 License expiry date, 2 License Renewal link */
				$expiry_details = sprintf( __( 'Your license has expired on %1$s. Please <a href="%2$s">renew it</a> to receive automatic updates and support.', 'email-log' ), $expiry_date, esc_url( $this->get_license()->get_renewal_link() ) );
			} else {
				/* translators: 1 License expiry date */
				$expiry_details = sprintf( __( 'Your license is valid till %s', 'email-log' ), $expiry_date );
			}
		}
		?>

		<span class="el-expander dashicons dashicons-arrow-<?php echo sanitize_html_class( $dashicon ); ?>"
			title="<?php _e( 'Individual add-on license', 'email-log' ); ?>"></span>

		<div class="individual-license <?php echo sanitize_html_class( $license_wrap ); ?>">
			<form method="post">
				<input type="text" name="el-license" class="el-license" size="40"
				       title="<?php _e( 'Email Log License Key', 'email-log' ); ?>"
				       placeholder="<?php echo esc_attr( sprintf( __( '%s Add-on License Key', 'email-log' ), $this->name ) ); ?>"
				       value="<?php echo esc_attr( $this->get_addon_license_key() ); ?>">

				<input type="submit" class="button <?php echo sanitize_html_class( $button_class ); ?>"
				       value="<?php echo esc_attr( $action_text ); ?>">

				<p class="expires"><?php echo $expiry_details; ?></p>

				<input type="hidden" name="el-addon" value="<?php echo esc_attr( $this->name ); ?>">
				<input type="hidden" name="el-action" value="<?php echo esc_attr( $action ); ?>">

				<?php wp_nonce_field( $action, $action . '_nonce' ); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Is the add-on installed?
	 *
	 * @return bool True, if installed. False otherwise.
	 */
	public function is_installed() {
		$installed_plugins = array_keys( get_plugins() );

		return in_array( $this->file, $installed_plugins, true );
	}

	/**
	 * Get the version of the add-on.
	 * If the add-on is installed then it returns the installed version,
	 * otherwise returns the latest add-on version from server.
	 *
	 * @return string Add-on version.
	 */
	public function get_version() {
		if ( ! $this->is_installed() ) {
			return $this->version;
		}

		$plugins_data = get_plugins();

		return $plugins_data[ $this->file ]['Version'];
	}

	/**
	 * Is the add-on active?
	 *
	 * @return bool True if the add-on is active, False otherwise.
	 */
	public function is_active() {
		return is_plugin_active( $this->file );
	}

	/**
	 * Get the activate url for the add-on.
	 *
	 * @return string Activate url with nonce.
	 */
	protected function get_activate_url() {
		return wp_nonce_url( network_admin_url( 'plugins.php?action=activate&amp;plugin=' . $this->file ), 'activate-plugin_' . $this->file );
	}

	/**
	 * Get the install url for the add-on.
	 *
	 * @return string Install url with nonce.
	 */
	protected function get_install_url() {
		return wp_nonce_url( network_admin_url( 'update.php?action=install-plugin&plugin=' . $this->slug ), 'install-plugin_' . $this->slug );
	}

	/**
	 * Get the download url for add-on.
	 *
	 * @return string Download url for add-on.
	 */
	public function get_download_url() {
		$licenser = $this->email_log->get_licenser();

		if ( is_null( $licenser ) ) {
			return '';
		}

		return $licenser->get_addon_download_url( $this->slug );
	}

	/**
	 * Is there a valid bundle license?
	 *
	 * @return bool True if valid, False otherwise.
	 */
	protected function has_valid_bundle_license() {
		$licenser = $this->email_log->get_licenser();

		if ( is_null( $licenser ) ) {
			return false;
		}

		return $licenser->is_bundle_license_valid();
	}

	/**
	 * Is the license of this add-on valid?
	 *
	 * @return bool True if valid, False otherwise.
	 */
	protected function has_valid_addon_license() {
		return $this->get_license()->is_valid();
	}

	/**
	 * Get license key if the add-on has a valid license.
	 *
	 * @return string|null License key if found, null otherwise.
	 */
	public function get_addon_license_key() {
		return $this->get_license()->get_license_key();
	}

	/**
	 * Parse and store add-on data from data array.
	 *
	 * @param array $data Data array.
	 */
	protected function parse_data( $data ) {
		$this->name        = $data['info']['title'];
		$this->version     = $data['info']['version'];
		$this->thumbnail   = $data['info']['thumbnail'];
		$this->description = $data['info']['excerpt'];
		$this->link        = $data['info']['permalink'];
		$this->slug        = 'email-log-' . $data['info']['slug'];
		$this->file        = sprintf( '%1$s/%1$s.php', $this->slug );
		$this->author      = 'Sudar Muthu';
	}
}
