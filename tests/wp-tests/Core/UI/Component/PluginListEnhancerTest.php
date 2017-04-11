<?php namespace EmailLog\Core\UI\Component;

use EmailLog\Core\UI\Component\AdminUIEnhancer;

if ( ! class_exists( 'EmailLog\Core\UI\Components\PluginListEnhancer' ) ) {
	return;
}

/**
 * Mock object for PluginListEnhancerClass.
 *
 * We need a mock object, since we need to access plugin basename.
 */
class MockAdminUIEnhancerClass extends AdminUIEnhancer {
	public function get_plugin_basename() {
		return $this->plugin_basename;
	}
}

/**
 * Test PluginList Enhancer.
 *
 * @since 2.0
 */
class PluginListEnhancerTest extends \PHPUnit_Framework_TestCase {

	protected $file;
	protected $plugin_list_enhancer;

	public function setUp() {
		parent::setUp();
		$this->file = str_replace( 'tests/wp-tests/Core/UI/', '', __FILE__ );

		$this->plugin_list_enhancer = new MockAdminUIEnhancerClass( $this->file );
	}

	public function test_plugin_basename() {
		$expected = plugin_basename( $this->file );
		$actual   = $this->plugin_list_enhancer->get_plugin_basename();

		$this->assertEquals( $expected, $actual );
	}

	public function test_row_meta_filter_not_changed_for_other_plugin() {
		$links  = array();
		$plugin = 'some-other-plugin';

		$actual = $this->plugin_list_enhancer->insert_addon_link( $links, $plugin );

		$this->assertcount( count( $links ), $actual );
	}

	public function test_row_meta_filter_changed_for_plugin() {
		$links  = array();
		$plugin = $this->plugin_list_enhancer->get_plugin_basename();

		$actual = $this->plugin_list_enhancer->insert_addon_link( $links, $plugin );

		$this->assertcount( count( $links ) + 1, $actual );
	}

	public function test_link_is_inserted() {
		$links = array();

		$actual = $this->plugin_list_enhancer->insert_manage_log_link( $links );

		$this->assertCount( count( $links ) + 1, $actual );
	}
}
