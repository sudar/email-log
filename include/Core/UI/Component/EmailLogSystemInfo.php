<?php

namespace EmailLog\Core\UI\Component;

/**
 * Email Log System Info.
 *
 * @see \EmailLog\Core\UI\Component\SystemInfo
 *
 * @since 2.3.0
 */
class EmailLogSystemInfo extends SystemInfo {

	/**
	 * Setup hooks and filters.
	 */
	public function load() {
		add_action( 'system_info_before', array( $this, 'print_version' ), 10, 2 );
	}

	public function print_version() {
		?>
Email Log Version:        <?php echo $this->get_plugin_version(); ?>
		<?php
	}

	protected function get_default_config() {
		$config = parent::get_default_config();

		$config['show_posts']      = false;
		$config['show_taxonomies'] = false;

		return $config;
	}

	protected function get_plugin_version() {
		$plugin_path = WP_PLUGIN_DIR . '/email-log/email-log.php';
		$plugin_data = get_plugin_data( $plugin_path );

		return $plugin_data['Version'];
	}
}
