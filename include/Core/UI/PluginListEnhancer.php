<?php namespace EmailLog\Core\UI;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Enhance Plugin List UI.
 *
 * @since   2.0
 * @package EmailLog\Core\UI
 */
class PluginListEnhancer {

	/**
	 * @var string Plugin basename.
	 */
	protected $plugin_basename;

	/**
	 * Initialize the plugin.
	 */
	public function __construct( $file ) {
		$this->plugin_basename = plugin_basename( $file );
	}

	/**
	 * Setup hooks.
	 */
	public function load() {
		add_filter( 'plugin_row_meta', array( $this, 'insert_addon_link' ), 10, 2 );
		add_filter( 'plugin_action_links_' . $this->plugin_basename, array( $this, 'insert_manage_log_link' ) );
	}

	/**
	 * Add link to Add-ons page.
	 *
	 * @see  Additional links in the Plugin listing is based on
	 * @link http://zourbuth.com/archives/751/creating-additional-wordpress-plugin-links-row-meta/
	 *
	 * @param array  $links Array with default links to display in plugins page.
	 * @param string $file  The name of the plugin file.
	 *
	 * @return array Modified list of links to display in plugins page.
	 */
	public function insert_addon_link( $links, $file ) {
		if ( $file == $this->plugin_basename ) {
			$links[] = '<a href="http://sudarmuthu.com/wordpress/email-log/pro-addons" target="_blank">' . __( 'Buy Addons', 'email-log' ) . '</a>';
		}

		return $links;
	}

	/**
	 * Add link to 'Manage log' page in plugin listing page.
	 *
	 * @param array $links List of links.
	 *
	 * @return array Modified list of links.
	 */
	public function insert_manage_log_link( $links ) {
		$settings_link = '<a href="admin.php?page=email-log">' . __( 'View Logs', 'email-log' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}
}