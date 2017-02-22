<?php namespace EmailLog\Core;

/**
 * Test main plugin class.
 */
class EmailLogTest extends \WP_UnitTestCase {
	protected $email_log;
	protected $file;

	public function setUp() {
		parent::setUp();

		$this->file = str_replace( 'tests/wp-tests/Core/', '', __FILE__ );

		// Create a stub for EmailLogAutoloader class.
		$loader_stub = $this->getMockBuilder( '\\EmailLog\\EmailLogAutoloader' )->getMock();

		// Create a stub for Table Manager class.
		$table_manager_stub = $this->getMockBuilder( '\\EmailLog\\Core\\DB\\TableManager' )->getMock();
		$table_manager_stub->method( 'load' );

		// Create a stub for Email Logger class.
		$email_logger_stub = $this->getMockBuilder( '\\EmailLog\\Core\\EmailLogger' )->getMock();
		$email_logger_stub->method( 'load' );

		$this->email_log = new EmailLog( $this->file, $loader_stub, $table_manager_stub );
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
