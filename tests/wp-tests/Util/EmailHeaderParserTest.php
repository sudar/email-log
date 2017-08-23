<?php namespace EmailLog\Util;

/**
 * Test Email_Header_Parser class methods.
 */
class EmailHeaderParserTest extends \WP_UnitTestCase {

	protected $object;

	public function setUp() {
		parent::setUp();
		$this->object = new EmailHeaderParser();
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Test `join_headers` method.
	 *
	 * Include all the headers in the mock data.
	 */
	function test_join_headers_with_all_data() {
		$mock_data = array(
			'from'         => 'mariadanieldeepak@gmail.com',
			'cc'           => 'maria.danieldeepak@gmail.com',
			'bcc'          => 'mariadaniel.deepak@gmail.com',
			'reply_to'     => 'mariadanieldeepak@gmail.com',
			'content_type' => 'text/html; charset=iso-8859-1',
		);

		$line_break  = "\r\n";
		$expected    = 'From: mariadanieldeepak@gmail.com' . $line_break;
		$expected   .= 'CC: maria.danieldeepak@gmail.com' . $line_break;
		$expected   .= 'BCC: mariadaniel.deepak@gmail.com' . $line_break;
		$expected   .= 'Reply-to: mariadanieldeepak@gmail.com' . $line_break;
		$expected   .= 'Content-type: text/html; charset=iso-8859-1' . $line_break;
		$actual      = $this->object->join_headers( $mock_data );

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test `join_headers` method.
	 *
	 * Include few headers in the mock data.
	 */
	function test_join_headers_with_few_data() {
		$mock_data = array(
			'from'         => 'mariadanieldeepak@gmail.com',
			'reply_to'     => 'mariadanieldeepak@gmail.com'
		);

		$line_break  = "\r\n";
		$expected    = 'From: mariadanieldeepak@gmail.com' . $line_break;
		$expected   .= 'Reply-to: mariadanieldeepak@gmail.com' . $line_break;
		$actual      = $this->object->join_headers( $mock_data );

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test `join_headers` method.
	 *
	 * Test with mock data that has no header information.
	 */
	function test_join_headers_with_empty_data() {
		$mock_data = array(
			'from'         => '',
			'cc'           => '',
			'bcc'          => '',
			'reply_to'     => '',
			'content_type' => '',
		);

		$expected = '';
		$actual   = $this->object->join_headers( $mock_data );

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test `parse_headers` method.
	 *
	 * Test with all headers.
	 */
	function test_parse_with_all_data() {
		$line_break    = "\r\n";
		$mock_headers  = 'From: mariadanieldeepak@gmail.com' . $line_break;
		$mock_headers .= 'CC: maria.danieldeepak@gmail.com' . $line_break;
		$mock_headers .= 'BCC: mariadaniel.deepak@gmail.com' . $line_break;
		$mock_headers .= 'Reply-to: mariadanieldeepak@gmail.com' . $line_break;
		$mock_headers .= 'Content-type: text/html; charset=iso-8859-1' . $line_break;

		$expected = array(
			'from'         => 'mariadanieldeepak@gmail.com',
			'cc'           => 'maria.danieldeepak@gmail.com',
			'bcc'          => 'mariadaniel.deepak@gmail.com',
			'reply_to'     => 'mariadanieldeepak@gmail.com',
			'content_type' => 'text/html; charset=iso-8859-1',
		);
		$actual = $this->object->parse_headers( $mock_headers );

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test `parse_headers` method.
	 *
	 * Test with quotes in headers.
	 */
	function test_parse_with_quotes() {
		$line_break    = "\r\n";
		$mock_headers  = 'From: "Maria Daniel" <mariadanieldeepak@gmail.com>' . $line_break;
		$mock_headers .= 'CC: maria.danieldeepak@gmail.com' . $line_break;
		$mock_headers .= 'BCC: mariadaniel.deepak@gmail.com' . $line_break;
		$mock_headers .= 'Reply-to: "Maria Daniel" <mariadanieldeepak@gmail.com>' . $line_break;
		$mock_headers .= 'Content-type: text/html; charset=iso-8859-1' . $line_break;

		$expected = array(
			'from'         => '"Maria Daniel" <mariadanieldeepak@gmail.com>',
			'cc'           => 'maria.danieldeepak@gmail.com',
			'bcc'          => 'mariadaniel.deepak@gmail.com',
			'reply_to'     => '"Maria Daniel" <mariadanieldeepak@gmail.com>',
			'content_type' => 'text/html; charset=iso-8859-1',
		);
		$actual = $this->object->parse_headers( $mock_headers );

		$this->assertEquals( $expected, $actual );
	}
	/**
	 * Test `parse_headers` method.
	 *
	 * Test with few headers information.
	 */
	function test_parse_with_few_data() {
		$line_break    = "\r\n";
		$mock_headers  = 'CC: maria.danieldeepak@gmail.com' . $line_break;
		$mock_headers .= 'BCC: mariadaniel.deepak@gmail.com' . $line_break;
		$mock_headers .= 'Content-type: text/html; charset=iso-8859-1' . $line_break;

		$expected = array(
			'cc'           => 'maria.danieldeepak@gmail.com',
			'bcc'          => 'mariadaniel.deepak@gmail.com',
			'content_type' => 'text/html; charset=iso-8859-1',
		);
		$actual = $this->object->parse_headers( $mock_headers );

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test `parse_headers` method.
	 *
	 * Test with no header information.
	 */
	function test_parse_with_no_data() {
		$mock_headers  = '';
		$expected = array();
		$actual = $this->object->parse_headers( $mock_headers );

		$this->assertEquals( $expected, $actual );
	}
}
