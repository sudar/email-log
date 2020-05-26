<?php namespace EmailLog\Core\UI\ListTable;

// TODO: Add test for `prepare_items` method
// TODO: Add tests for other public methods
use EmailLog\Core\UI\ListTable\LogListTable;
use EmailLog\Core\UI\Page\LogListPage;

if ( ! class_exists( 'EmailLog\\Core\\UI\\Page\\LogListPage') ) {
	return;
}

class MockLogListPage extends LogListPage {
	public function get_screen() {
		return \WP_Screen::get( 'toplevel_page_email-log' );
	}
}

class LogListTableTest extends \WP_UnitTestCase {
	protected $file;
	protected $log_list_table;

	public function setUp() {
		parent::setUp();

		$this->file = str_replace( 'tests/wp-tests/Core/', '', __FILE__ );
		$page = new MockLogListPage( $this->file );
		$page->load();
		$this->log_list_table = new LogListTable( $page );
	}

	public function test_get_columns() {
		$actual = $this->log_list_table->get_columns();

		$this->assertArrayHasKey( 'cb', $actual );
		$this->assertArrayHasKey( 'sent_date', $actual );
		$this->assertArrayHasKey( 'to_email', $actual );
		$this->assertArrayHasKey( 'subject', $actual );
	}
}
