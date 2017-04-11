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
	 * @abstract
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
	 *
	 * TODO: Change links used in this function.
	 */
	public function render_help_tab() {
		/**
		 * Content specified inline
		 */
		$this->get_screen()->add_help_tab(
			array(
				'title'    => __( 'About Plugin', 'email-log' ),
				'id'       => 'about_tab',
				'content'  => '<p>' . __( 'Email Log WordPress Plugin, allows you to log all emails that are sent through WordPress.', 'email-log' ) . '</p>',
				'callback' => false,
			)
		);

		// Add help sidebar.
		// TODO: Change the links.
		$this->get_screen()->set_help_sidebar(
			'<p><strong>' . __( 'More information', 'email-log' ) . '</strong></p>' .
			'<p><a href = "http://sudarmuthu.com/wordpress/email-log">' . __( 'Plugin Homepage/support', 'email-log' ) . '</a></p>' .
			'<p><a href = "http://sudarmuthu.com/blog">' . __( "Plugin author's blog", 'email-log' ) . '</a></p>' .
			'<p><a href = "http://sudarmuthu.com/wordpress/">' . __( "Other Plugin's by Author", 'email-log' ) . '</a></p>'
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
