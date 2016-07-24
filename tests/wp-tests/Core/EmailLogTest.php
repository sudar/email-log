<?php namespace EmailLog\Core;

/**
 * Test main plugin class.
 */
class EmailLogTest extends \WP_UnitTestCase {
	protected $object;
	protected $file;

	public function setUp() {
		parent::setUp();
		$this->file   = str_replace( 'tests/wp-tests/Core/', '', __FILE__ );
		$this->object = new EmailLog( $this->file );
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Test translations_path.
	 */
	public function test_translations_path() {
		$this->object->load();

		$expected = dirname( plugin_basename( $this->file ) ) . '/languages/';
		$actual   = $this->object->translations_path;

		$this->assertEquals( $expected, $actual );
	}
}
