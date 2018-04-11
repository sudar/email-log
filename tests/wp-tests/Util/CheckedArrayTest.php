<?php namespace EmailLog\Util;

/**
 * Test `checked_array` functions inside helper.
 */
class CheckedArrayTest extends \WP_UnitTestCase {

	function test_value_is_array() {
		$current = 'editor';
		$values  = 'author';

		$expected = '';
		$actual = checked_array( $values, $current );

		$this->assertEquals( $expected, $actual );
	}

	function test_value_is_exist() {
		$current = 'editor';
		$values  = array( 'author', 'editor' );

		$expected = 'checked="checked"';
		$actual = checked_array( $values, $current );

		$this->assertEquals( $expected, $actual );
	}
}

