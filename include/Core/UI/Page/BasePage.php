<?php namespace EmailLog\Core\UI\Page;

/**
 * Base class for all admin pages.
 *
 * @since 2.0
 */
abstract class BasePage {
	/**
	 * @var string Plugin filename.
	 */
	protected $plugin_file;

	/**
	 * @var string Current page.
	 */
	protected $page;

	/**
	 * @var \WP_Screen Current screen.
	 */
	protected $screen;

	/**
	 * Register page.
	 *
	 * @abstract
	 */
	abstract public function register_page();

	/**
	 * LogListPage constructor.
	 *
	 * @param string $file Plugin file
	 */
	public function __construct( $file ) {
		$this->plugin_file = $file;
	}

	/**
	 * Setup hooks.
	 */
	protected function load() {
		add_action( 'admin_menu', array( $this, 'register_page' ) );
	}

	/**
	 * Render help tab.
	 */
	protected function render_help_tab() {
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

		// Add help sidebar
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

		// Display credits in Footer
		add_action( 'in_admin_footer', array( $this, 'add_footer_links' ) );
	}

	/**
	 * Adds Footer links.
	 *
	 * @since Genesis
	 *
	 * @see   Function relied on
	 * @link  http://striderweb.com/nerdaphernalia/2008/06/give-your-wordpress-plugin-credit/
	 */
	public function add_footer_links() {
		$plugin_data = get_plugin_data( $this->plugin_file );
		printf(
			'%1$s ' . __( 'plugin', 'email-log' ) . ' | ' . __( 'Version', 'email-log' ) . ' %2$s | ' . __( 'by', 'email-log' ) . ' %3$s<br />',
			$plugin_data['Title'],
			$plugin_data['Version'],
			$plugin_data['Author']
		);
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