<?php
/**
 * Class SampleTest
 *
 * @package Email_Log
 */

require_once( dirname( dirname( __FILE__ ) ) . '/email-log.php' );

/**
 * Sample test case.
 */
class Email_Log_Tests extends WP_UnitTestCase {

	/**
	 * A single example test.
	 */
	function test_sample() {
		// Replace this with some actual testing code.
		$this->assertTrue( true );
	}

	function test_add_plugin_links() {
		$links = add_plugin_links($links, $file);
	}
}
