<?php namespace EmailLog\Util;

/**
 * Test `sanitize_email` and `sanitize_email_with_name` functions inside helper.
 */
class SanitizeEmailTest extends \WP_UnitTestCase {

	function test_email_is_valid() {
		$invalid_email = 'sudar@sudarmuthu';

		$expected = '';
		$actual_1 = sanitize_email( $invalid_email );

		$this->assertEquals( $expected, $actual_1 );
	}

	function test_email_with_name() {
		$email_with_name = 'Sudar Muthu<sudar@sudarmuthu.com>';

		$expected = 'Sudar Muthu <sudar@sudarmuthu.com>';
		$actual_1 = sanitize_email( $email_with_name );
		$actual_2 = sanitize_email_with_name( $email_with_name );

		$this->assertEquals( $expected, $actual_1 );
		$this->assertEquals( $expected, $actual_2 );
	}

	function test_email_with_name_and_space() {
		$email_with_name_and_space = ' Sudar Muthu <sudar@sudarmuthu.com>';

		$expected = 'Sudar Muthu <sudar@sudarmuthu.com>';
		$actual_1 = sanitize_email( $email_with_name_and_space );
		$actual_2 = sanitize_email_with_name( $email_with_name_and_space );

		$this->assertEquals( $expected, $actual_1 );
		$this->assertEquals( $expected, $actual_2 );
	}

	function test_multiple_simple_email_returns_first() {
		$multiple_emails = 'sudar@sudarmuthu.com, muthu@sudarmuthu.com';

		$expected = 'sudar@sudarmuthu.com';
		$actual   = sanitize_email( $multiple_emails, false );

		$this->assertEquals( $expected, $actual );
	}

	function test_trimed_multiple_simple_email_returns_first() {
		$multiple_emails = '  sudar@sudarmuthu.com,  muthu@sudarmuthu.com  ';

		$expected = 'sudar@sudarmuthu.com';
		$actual   = sanitize_email( $multiple_emails, false );

		$this->assertEquals( $expected, $actual );
	}

	function test_multiple_simple_email() {
		$multiple_emails = 'sudar@sudarmuthu.com, muthu@sudarmuthu.com';

		$expected = 'sudar@sudarmuthu.com, muthu@sudarmuthu.com';
		$actual   = sanitize_email( $multiple_emails );

		$this->assertEquals( $expected, $actual );
	}

	function test_trimed_multiple_simple_email() {
		$multiple_emails = '   sudar@sudarmuthu.com   ,   muthu@sudarmuthu.com   ';

		$expected = 'sudar@sudarmuthu.com, muthu@sudarmuthu.com';
		$actual   = sanitize_email( $multiple_emails );

		$this->assertEquals( $expected, $actual );
	}

	function test_multiple_email_with_name() {
		$multiple_emails = 'Sudar Muthu <sudar@sudarmuthu.com>, Muthu<muthu@sudarmuthu.com>';

		$expected = 'Sudar Muthu <sudar@sudarmuthu.com>, Muthu <muthu@sudarmuthu.com>';
		$actual   = sanitize_email( $multiple_emails );

		$this->assertEquals( $expected, $actual );
	}

	function test_multiple_email_with_name_and_quotes() {
		$multiple_emails = '"Sudar Muthu" <sudar@sudarmuthu.com>, "Muthu"<muthu@sudarmuthu.com>';

		$expected = '"Sudar Muthu" <sudar@sudarmuthu.com>, "Muthu" <muthu@sudarmuthu.com>';
		$actual   = sanitize_email( $multiple_emails );

		$this->assertEquals( $expected, $actual );
	}

	function test_multiple_email_with_name_returns_first() {
		$multiple_emails = 'Sudar Muthu <sudar@sudarmuthu.com>, Muthu <muthu@sudarmuthu.com>';

		$expected = 'Sudar Muthu <sudar@sudarmuthu.com>';
		$actual   = sanitize_email( $multiple_emails, false );

		$this->assertEquals( $expected, $actual );
	}
}

