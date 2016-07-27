<?php namespace EmailLog\Core;

// TODO: Add test to `log_email` method that tests the parsing of passed `mail_info`
// TODO: Add integration tests to test the filter
class EmailLoggerTest extends \PHPUnit_Framework_TestCase {
	protected $logger;

	public function setUp() {
		parent::setUp();

		$email_log = email_log();

		// Create a stub for Table Manager class.
		$table_manager_stub = $this->getMockBuilder( '\\EmailLog\\Core\\DB\\TableManager' )->getMock();
		$table_manager_stub->method( 'insert_log' );

		$email_log->table_manager = $table_manager_stub;

		$this->logger = new EmailLogger();
	}

	public function test_filter_doesnt_change_mailinfo() {
		$mail_info = array(
			'attachments' => array(),
			'to' => array( 'sudar@sudarmuthu.com' ),
			'subject' => 'Email subject',
			'headers' => array(),
		);

		$actual = $this->logger->log_email( $mail_info );

		$this->assertEquals( $mail_info, $actual );
	}
}
