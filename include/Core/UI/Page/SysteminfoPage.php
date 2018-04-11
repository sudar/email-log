<?php namespace EmailLog\Core\UI\Page;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Settings Page.
 * This page is displayed only if any add-on has a setting enabled.
 *
 * @since 2.0.0
 */
class SysteninfoPage extends BasePage {

	/**
	 * Page slug.
	 */
	const PAGE_SLUG = 'email-log-info';

	/**
	 * Register page.
	 *
	 * @inheritdoc
	 */
	public function register_page() {
		$this->label = array(
			'page_title' => __( 'Bulk Delete - System Info', 'bulk-delete' ),
			'menu_title' => __( 'System Info', 'bulk-delete' ),
		);

		$this->messages = array(
			'info_message' => __( 'Please include this information when posting support requests.', 'bulk-delete' ),
		);

		add_action( 'bd_download_sysinfo', array( $this, 'generate_sysinfo_download' ) );
	}
}
