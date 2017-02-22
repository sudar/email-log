<?php namespace EmailLog\Core\UI;

use EmailLog\Core\Loadie;

/**
 * Admin UI Loader.
 * Loads and initializes all admin pages and components.
 *
 * @since 2.0
 */
class UILoader implements Loadie {

	/**
	 * Plugin filename.
	 *
	 * @var string
	 */
	protected $plugin_file;

	/**
	 * UI Component List.
	 *
	 * @var array
	 */
	protected $components = array();

	/**
	 * List of Admin pages.
	 * @var array
	 */
	protected $pages = array();

	/**
	 * Setup UI Loader.
	 *
	 * @param string $file Plugin main file.
	 */
	public function __construct( $file ) {
		$this->plugin_file = $file;
	}

	/**
	 * Load all components and setup hooks.
	 */
	public function load() {
		$this->initialize_components();
		$this->initialize_pages();

		foreach ( $this->components as $component ) {
			$component->load();
		}

		foreach ( $this->pages as $page ) {
			$page->load();
		}
	}

	/**
	 * Initialize UI component Objects.
	 *
	 * This method may be overwritten in tests.
	 *
	 * @access protected
	 */
	protected function initialize_components() {
		$this->components['plugin_list_enhancer'] = new Component\PluginListEnhancer( $this->plugin_file );
	}

	/**
	 * Initialize Admin page Objects.
	 *
	 * This method may be overwritten in tests.
	 *
	 * @access protected
	 */
	protected function initialize_pages() {
		$this->pages['log_list_page']   = new Page\LogListPage( $this->plugin_file );
		$this->pages['addon_list_page'] = new Page\AddonListPage( $this->plugin_file );
		$this->pages['settings_page']   = new Page\SettingsPage( $this->plugin_file );
	}
}
