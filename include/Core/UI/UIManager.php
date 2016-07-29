<?php namespace EmailLog\Core\UI;

/**
 * Admin UI Manager.
 *
 * @since 2.0
 */
class UIManager {

	/**
	 * @var string Plugin filename.
	 */
	protected $plugin_file;

	/**
	 * @var array UI Components List.
	 */
	protected $components = array();

	/**
	 * Initialize the plugin.
	 */
	public function __construct( $file ) {
		$this->plugin_file = $file;
	}

	/**
	 * Load all components and setup hooks.
	 */
	public function load() {
		$this->initialize_components();

		foreach ( $this->components as $component ) {
			$component->load();
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
		$this->components['plugin_list_enhancer'] = new PluginListEnhancer( $this->plugin_file );
		$this->components['log_list_page']        = new Page\LogListPage( $this->plugin_file );
	}
}