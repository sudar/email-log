<?php namespace EmailLog\Core\UI\Page;

use EmailLog\Core\Loadie;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Base class for all Email Log admin pages.
 *
 * @since 2.0.0
 */
abstract class BasePage implements Loadie {

	/**
	 * Current page.
	 *
	 * @var string
	 */
	protected $page;

	/**
	 * Current screen.
	 *
	 * @var \WP_Screen
	 */
	protected $screen;

	/**
	 * Register page.
	 *
	 * @return void
	 */
	abstract public function register_page();

	/**
	 * Setup hooks related to pages.
	 *
	 * @inheritdoc
	 */
	public function load() {
		add_action( 'admin_menu', array( $this, 'register_page' ) );
	}

	/**
	 * Render help tab.
	 */
	public function render_help_tab() {
		$content = '<p>' .
					__( 'Email Log is a WordPress plugin that allows you to easily log and view all emails sent from WordPress.', 'email-log' ) .
				'</p>' .
				'<p>' .
					__( 'You can view the logged emails from the View Logs screen. ', 'email-log' ) .
					sprintf(
						__( 'Check the <a target="_blank" rel="noopener" href="%s">documentation about the View Logs screen</a> for more details.', 'email-log' ),
						'https://wpemaillog.com/docs/view-logged-email/?utm_campaign=Upsell&utm_medium=wpadmin&utm_source=help&utm_content=docs-content'
					) .
				'</p>' .
				'<p>' .
					sprintf(
						__( 'You can perform advanced actions like re-sending email, automatically forwarding emails or export logs with our <a target="_blank" rel="noopener" href="%s">premium plugins</a>.', 'email-log' ),
						'https://wpemaillog.com/store/?utm_campaign=Upsell&utm_medium=wpadmin&utm_source=help&utm_content=store-content'
					) .
				'</p>';

		$this->get_screen()->add_help_tab(
			array(
				'title'    => __( 'About Plugin', 'email-log' ),
				'id'       => 'about_tab',
				'content'  => $content,
				'callback' => false,
			)
		);

		$this->get_screen()->set_help_sidebar(
			'<p><strong>' . __( 'More information', 'email-log' ) . '</strong></p>' .
			'<p><a target="_blank" rel="noopener" href = "https://wpemaillog.com/docs/?utm_campaign=Upsell&utm_medium=wpadmin&utm_source=help&utm_content=docs">' . __( 'Documentation', 'email-log' ) . '</a></p>' .
			'<p><a target="_blank" rel="noopener" href = "https://wpemaillog.com/support/?utm_campaign=Upsell&utm_medium=wpadmin&utm_source=help&utm_content=support">' . __( 'Support', 'email-log' ) . '</a></p>' .
			'<p><a target="_blank" rel="noopener" href = "https://wpemaillog.com/store/?utm_campaign=Upsell&utm_medium=wpadmin&utm_source=help&utm_content=store">' . __( 'Add-ons', 'email-log' ) . '</a></p>'
		);
	}

	/**
	 * Render admin page footer.
	 */
	protected function render_page_footer() {
		/**
		 * Action to add additional content to email log admin footer.
		 *
		 * @since 1.8
		 */
		do_action( 'el_admin_footer' );
	}

	/**
	 * Return the WP_Screen object for the current page's handle.
	 *
	 * @return \WP_Screen Screen object.
	 */
	public function get_screen() {
		if ( ! isset( $this->screen ) ) {
			$this->screen = \WP_Screen::get( $this->page );
		}

		return $this->screen;
	}
}
