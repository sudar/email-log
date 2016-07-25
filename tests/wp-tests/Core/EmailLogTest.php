<?php namespace EmailLog\Core;

/**
 * Test main plugin class.
 */
class EmailLogTest extends \WP_UnitTestCase {
	protected $email_log;
	protected $file;

	public function setUp() {
		parent::setUp();
		$this->file      = str_replace( 'tests/wp-tests/Core/', '', __FILE__ );
		$this->email_log = new EmailLog( $this->file );

		// Create a stub for Table Manager class.
		$table_manager_stub = $this->getMockBuilder( DB\TableManager::class )->getMock();
		$table_manager_stub->method( 'load' );

		$this->email_log->table_manager = $table_manager_stub;
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Test translations_path.
	 */
	public function test_translations_path() {
		$this->email_log->load();

		$expected = dirname( plugin_basename( $this->file ) ) . '/languages/';
		$actual   = $this->email_log->translations_path;

		$this->assertEquals( $expected, $actual );
	}
}
