<?php namespace EmailLog\Util;

/**
 * Test `checked_array` functions inside helper.
 */
class CheckCheckboxTest extends \WP_UnitTestCase {

	function test_value_is_array() {
		$current = 'editor';
		$values  = 'author';

		$expected = '';
		$actual = checked_array( $values, $current );

		$this->assertEquals( $expected, $actual );
	}
}

