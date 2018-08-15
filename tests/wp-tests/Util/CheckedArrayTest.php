<?php namespace EmailLog\Util;

/**
 * Test `checked_array` functions inside helper.
 */
class CheckedArrayTest extends \WP_UnitTestCase {

	/**
	 * Data provider to `checked_array()`
	 *
	 * @see CheckedArrayTest::test_checked_array() To see how the data is used.
	 *
	 * @return array
	 */
	function provider_to_test_checked_array() {
		return array(
			array(
				array(
					'values'  => array( 'editor', 'author', 'subscriber' ),
					'current' => 'editor'
				),
				'checked="checked"'
			),
			array(
				array(
					'values'  => 'editor',
					'current' => 'editor'
				),
				''
			)
		);
	}

	/**
	 * Test $values is array.
	 *
	 * @dataProvider provider_to_test_checked_array
	 */
	function test_checked_array( $input, $expected ) {
		ob_start();
		checked_array( $input['values'], $input['current'] );
		$actual = ob_get_clean();
		$this->assertEquals( $expected, $actual );
	}
}

