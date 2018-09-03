<?php namespace EmailLog\Core\UI;

use EmailLog\Core\Loadie;
use EmailLog\Core\UI\Page\LogListPage;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

/**
 * Admin UI Loader.
 * Loads and initializes all admin pages and components.
 *
 * @since 2.0
 */
class UILoader implements Loadie {

	/**
	 * UI Component List.
	 *
	 * @var array
	 */
	protected $components = array();

	/**
	 * List of Admin pages.
	 *
	 * @var \EmailLog\Core\UI\Page\BasePage[]
	 */
	protected $pages = array();

	/**
	 * Load all components and setup hooks.
	 *
	 * @inheritdoc
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

	public function is_show_dashboard_widget() {
		$this->components['core_settings'] = new Setting\CoreSetting();
		$dashboard_status                  = false;
		$options                           = get_option( 'email-log-core' );
		if( isset( $options['hide_dashboard_widget'] ) ) {
			$dashboard_status = $options['hide_dashboard_widget'];
		}

		return $dashboard_status;
	}

	/**
	 * Initialize UI component Objects.
	 *
	 * This method may be overwritten in tests.
	 *
	 * @access protected
	 */
	protected function initialize_components() {
		if ( current_user_can( LogListPage::CAPABILITY ) ) {
			$this->components['admin_ui_enhancer'] = new Component\AdminUIEnhancer();
			if( ! $this->is_show_dashboard_widget() ) {
				$this->components['dashboard_widget']  = new Component\DashboardWidget();
			}
		}
	}

	/**
	 * Initialize Admin page Objects.
	 *
	 * This method may be overwritten in tests.
	 *
	 * @access protected
	 */
	protected function initialize_pages() {
		$this->pages['log_list_page']    = new Page\LogListPage();
		$this->pages['settings_page']    = new Page\SettingsPage();
		$this->pages['addon_list_page']  = new Page\AddonListPage();
		$this->pages['system_info_page'] = new Page\SystemInfoPage();
	}
}
