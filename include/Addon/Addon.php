<?php namespace EmailLog\Addon;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Encapsulate Add-on Data.
 *
 * @since 2.0.0
 */
class Addon {

	private $name;
	private $thumbnail;
	private $description;
	private $link;
	private $slug;
	private $file;

	/**
	 * Construct Addon object from data array.
	 *
	 * @param array $data Data array.
	 */
	public function __construct( $data ) {
		$this->name        = $data['info']['title'];
		$this->thumbnail   = $data['info']['thumbnail'];
		$this->description = $data['info']['excerpt'];
		$this->link        = $data['info']['permalink'];
		$this->slug        = 'email-log-' . $data['info']['slug'];
		$this->file        = sprintf( '%1$s/%1$s.php', $this->slug );
	}

	/**
	 * Render the add-on in Addon list page.
	 */
	public function render() {
		?>
		<div class="el-addon">
			<h3 class="el-addon-title"> <?php echo esc_html( $this->name ); ?> </h3>

			<a href="<?php echo esc_url( $this->link ); ?>" title="<?php echo esc_attr( $this->name ); ?>">
				<img src="<?php echo esc_url( $this->thumbnail ); ?>" class="attachment-showcase wp-post-image"
				     alt="<?php echo esc_attr( $this->name ); ?>" title="<?php echo esc_attr( $this->name ); ?>">
			</a>

			<p> <?php echo esc_html( $this->description ); ?> </p>

			<?php echo $this->get_actions(); ?>
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
	 * Get action links for add-ons.
	 *
	 * @return string Action links.
	 */
	protected function get_actions() {
		if ( ! $this->is_license_valid() ) {
			if ( $this->is_installed() ) {
				return sprintf(
					'<a disabled class="button-secondary" title="%s" href="#">%s</a>',
					__( 'You need an active license to install the add-on', 'email-log' ),
					_x( 'Activate License to Use', 'Download and activate addon', 'email-log' )
				);
			} else {
				return sprintf(
					'<a disabled class="button-secondary" title="%s" href="#">%s</a>',
					__( 'You need an active license to install the add-on', 'email-log' ),
					_x( 'Activate License to Install', 'Download and activate addon', 'email-log' )
				);
			}
		}

		if ( $this->is_installed() ) {
			$actions = '<a disabled class="button button-secondary">' . _x( 'Installed', 'Installed on website but not activated', 'email-log' );

			if ( is_plugin_active( $this->file ) ) {
				$actions .= ' &amp; ' . _x( 'Activated', 'Installed and activated on website', 'email-log' ) . '</a>';
			} else {
				$actions .= sprintf( '</a> <a class="button button-primary" href="%s">%s</a>', $this->get_activate_url(), _x( 'Activate', 'Enable addon so it may be used', 'email-log' ) );
			}
		} else {
			// TODO: Make sure WordPress core can handle add-on installation.
			$actions = sprintf( '<a class="button button-primary" href="%s">%s</a>', $this->get_install_url(), _x( 'Install', 'Download and activate addon', 'email-log' ) );
		}

		$actions .= sprintf( ' <a class="button button-secondary" target="_blank" href="%s">%s</a>', $this->get_download_url(), _x( 'Download', 'Download to your computer', 'email-log' ) );

		return $actions;
	}

	/**
	 * Is the add-on installed?
	 *
	 * @return bool True, if installed. False otherwise.
	 */
	protected function is_installed() {
		$installed_plugins = array_keys( get_plugins() );

		return in_array( $this->file, $installed_plugins, true );
	}

	/**
	 * Get teh activate url for the add-on.
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
	 * TODO: Link correct download url.
	 *
	 * @return string Download url for add-on.
	 */
	public function get_download_url() {
		$email_log = email_log();

		return $email_log->get_licenser()->get_addon_download_url( $this->name );
	}

	/**
	 * Is the license of this add-on valid?
	 *
	 * @return bool True if valid, False otherwise.
	 */
	protected function is_license_valid() {
		$email_log = email_log();

		return $email_log->get_licenser()->is_addon_license_valid( $this->name );
	}

	/**
	 * Get license key if the add-on has a valid license.
	 *
	 * @return bool|string License key if found, False otherwise.
	 */
	protected function get_license_key() {
		$email_log = email_log();

		return $email_log->get_licenser()->get_addon_license_key( $this->name );
	}
}
