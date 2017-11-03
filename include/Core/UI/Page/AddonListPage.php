<?php namespace EmailLog\Core\UI\Page;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Addon List Page.
 *
 * @since 2.0
 */
class AddonListPage extends BasePage {

	/**
	 * Page slug.
	 */
	const PAGE_SLUG = 'email-log-addons';

	/**
	 * Register page.
	 */
	public function register_page() {
		$this->page = add_submenu_page(
			LogListPage::PAGE_SLUG,
			__( 'Add-ons', 'email-log' ),
			__( 'Add-ons', 'email-log' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);

		add_action( "load-{$this->page}", array( $this, 'render_help_tab' ) );
		add_action( "load-{$this->page}", array( $this, 'enqueue_assets' ) );
	}

	/**
	 * Render the list of add-on in the page.
	 */
	public function render_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Email Log Add-ons', 'email-log' ); ?></h1>
			<?php settings_errors(); ?>

			<p>
				<?php _e( 'These add-ons provide additional functionality to Email Log plugin and are available for purchase.', 'email-log' ); ?>
				<?php _e( 'If your license includes the add-ons below, you will be able to install them from here with one-click.', 'email-log' ); ?>
			</p>

			<?php
			/**
			 * Before add-ons are listed in the add-on list page.
			 *
			 * @since 2.0.0
			 */
			do_action( 'el_before_addon_list' );

			$email_log = email_log();
			$licenser  = $email_log->get_licenser();
			if ( ! is_null( $licenser ) ) {
				$licenser->get_addon_list()->render();
			}
			?>
		</div>
		<?php

		$this->render_page_footer();
	}

	/**
	 * Enqueue static assets needed for this page.
	 */
	public function enqueue_assets() {
		$email_log = email_log();

		wp_enqueue_style( 'el_addon_list', plugins_url( 'assets/css/admin/addon-list.css', $email_log->get_plugin_file() ), array(), $email_log->get_version() );
		wp_enqueue_script( 'el_addon_list', plugins_url( 'assets/js/admin/addon-list.js', $email_log->get_plugin_file() ), array( 'jquery' ), $email_log->get_version(), true );
	}
}
