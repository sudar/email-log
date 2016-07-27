<?php namespace EmailLog\Core\UI;

// TODO: Add test for `prepare_items` method
// TODO: Add tests for other public methods
class LogTableTest extends \WP_UnitTestCase {
	protected $log_list_table;

	public function setUp() {
		parent::setUp();

		$this->log_list_table = new LogTable;
	}
}
