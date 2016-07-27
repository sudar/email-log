<?php namespace EmailLog\Core\UI;

// TODO: Add test for `prepare_items` method
// TODO: Add tests for other public methods
class LogListTableTest extends \WP_UnitTestCase {
	protected $log_list_table;

	public function setUp() {
		parent::setUp();

		$args = array(
			'screen' => 'tools_page_email-log',
		);

		$this->log_list_table = new LogListTable( $args );
	}

	public function test_get_columns() {
		$actual = $this->log_list_table->get_columns();

		$this->assertArrayHasKey( 'cb', $actual );
		$this->assertArrayHasKey( 'sent_date', $actual );
		$this->assertArrayHasKey( 'to', $actual );
		$this->assertArrayHasKey( 'subject', $actual );
	}
}
