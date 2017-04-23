<?php namespace EmailLog\Core\DB;

// TODO: Add tests for remaining methods
class TableManagerTest extends \WP_UnitTestCase {
	protected $table_manager;

	public function setUp() {
		parent::setUp();

		$this->table_manager = new TableManager;
	}

	public function test_get_log_table_name() {
		global $wpdb;

		$expected = $wpdb->prefix . 'email_log';
		$actual = $this->table_manager->get_log_table_name();

		$this->assertEquals( $expected, $actual );
	}

	public function test_on_delete_blog() {
		$tables = array(
			'some-table-name',
		);

		$table_name = $this->table_manager->get_log_table_name();
		$expected = array_merge( $tables, array( $table_name ) );

		$actual = $this->table_manager->delete_table_from_deleted_blog( $tables );

		$this->assertEquals( $expected, $actual );
	}
}
