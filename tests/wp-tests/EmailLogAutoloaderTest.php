<?php namespace EmailLog;

if ( ! class_exists( '\\EmailLog\\EmailLogAutoloader' ) ) {
	return;
}

/**
 * Mock class to test autoloader.
 * @package EmailLog
 */
class MockEmailLogAutoloaderClass extends EmailLogAutoloader {
	protected $class_files = array();

	public function set_class_files( array $class_files ) {
		$this->class_files = $class_files;
	}

	protected function require_file( $file ) {
		return in_array( $file, $this->class_files );
	}

	public function get_files() {
		return $this->files;
	}
}

/**
 * Test Autoloader
 * @package EmailLog
 */
class EmailLogAutoloaderTest extends \PHPUnit_Framework_TestCase {
	protected $loader;

	protected function setUp() {
		$this->loader = new MockEmailLogAutoloaderClass;

		$this->loader->set_class_files( array(
			'/vendor/foo.bar/src/ClassName.php',
			'/vendor/foo.bar/src/DoomClassName.php',
			'/vendor/foo.bar/tests/ClassNameTest.php',
			'/vendor/foo.bardoom/src/ClassName.php',
			'/vendor/foo.bar.baz.dib/src/ClassName.php',
			'/vendor/foo.bar.baz.dib.zim.gir/src/ClassName.php',
		) );

		$this->loader->add_namespace(
			'Foo\Bar',
			'/vendor/foo.bar/src'
		);

		$this->loader->add_namespace(
			'Foo\Bar',
			'/vendor/foo.bar/tests'
		);

		$this->loader->add_namespace(
			'Foo\BarDoom',
			'/vendor/foo.bardoom/src'
		);

		$this->loader->add_namespace(
			'Foo\Bar\Baz\Dib',
			'/vendor/foo.bar.baz.dib/src'
		);

		$this->loader->add_namespace(
			'Foo\Bar\Baz\Dib\Zim\Gir',
			'/vendor/foo.bar.baz.dib.zim.gir/src'
		);
	}

	public function testExistingFile() {
		$actual = $this->loader->load_class( 'Foo\Bar\ClassName' );
		$expect = '/vendor/foo.bar/src/ClassName.php';
		$this->assertSame( $expect, $actual );

		$actual = $this->loader->load_class( 'Foo\Bar\ClassNameTest' );
		$expect = '/vendor/foo.bar/tests/ClassNameTest.php';
		$this->assertSame( $expect, $actual );
	}

	public function testMissingFile() {
		$actual = $this->loader->load_class( 'No_Vendor\No_Package\NoClass' );
		$this->assertFalse( $actual );
	}

	public function testDeepFile() {
		$actual = $this->loader->load_class( 'Foo\Bar\Baz\Dib\Zim\Gir\ClassName' );
		$expect = '/vendor/foo.bar.baz.dib.zim.gir/src/ClassName.php';
		$this->assertSame( $expect, $actual );
	}

	public function testConfusion() {
		$actual = $this->loader->load_class( 'Foo\Bar\DoomClassName' );
		$expect = '/vendor/foo.bar/src/DoomClassName.php';
		$this->assertSame( $expect, $actual );

		$actual = $this->loader->load_class( 'Foo\BarDoom\ClassName' );
		$expect = '/vendor/foo.bardoom/src/ClassName.php';
		$this->assertSame( $expect, $actual );
	}

	/**
	 * Test autoloading of a file that doesn't exist.
	 */
	public function testFileAutoloadingFileNotExists() {
		$file_name = 'path/to/some/file';
		$this->loader->add_file( $file_name );

		$actual = in_array( $file_name, $this->loader->get_files() );

		$this->assertFalse( $actual );
	}

	/**
	 * Test autoloading of a file that exists.
	 */
	public function testFileAutoloadingFileExists() {
		$file_name = dirname( __FILE__ ) . '/bootstrap.php';
		$this->loader->add_file( $file_name );

		$actual = in_array( $file_name, $this->loader->get_files() );

		$this->assertTrue( $actual );
	}
}
