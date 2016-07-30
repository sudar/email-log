<?php namespace EmailLog\Core\UI\Page;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Addon List Page
 *
 * @since 2.0
 */
class AddonListPage extends BasePage {
	/**
	 * Page slug.
	 */
	const PAGE_SLUG = 'email-log-addon-list';

	/**
	 * Setup hooks.
	 */
	public function load() {
		parent::load();
	}

	/**
	 * Register page.
	 *
	 * @inheritdoc
	 */
	public function register_page() {
		$this->page = add_submenu_page(
			LogListPage::PAGE_SLUG,
			__( 'Addons', 'bulk-delete' ),
			__( 'Addons', 'bulk-delete' ),
			'manage_options',
			self::PAGE_SLUG,
			array( $this, 'render_page' )
		);

		add_action( "load-{$this->page}", array( $this, 'load_page' ) );
	}

	/**
	 * Render page.
	 */
	public function render_page() {
		?>
		<div class="wrap">
			<h2><?php _e( 'Email Log Addons', 'email-log' ); ?></h2>
			<?php settings_errors(); ?>

		</div>
		<?php
	}

	/**
	 * Load page.
	 */
	public function load_page() {
		$this->render_help_tab();

	}
}