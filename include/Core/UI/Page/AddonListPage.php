<?php namespace EmailLog\Core\UI\Page;

use EmailLog\Core\UI\Component\AddonListRenderer;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Addon List Page
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
	}

	/**
	 * Render the list of add-on in the page.
	 */
	public function render_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Email Log Add-ons', 'email-log' ); ?></h1>
			<?php settings_errors(); ?>
			<?php
				$addon_list_renderer = new AddonListRenderer( $this->plugin_file );
				$addon_list_renderer->render();
			?>
		</div>
		<?php
	}
}
