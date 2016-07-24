<?php namespace EmailLog;

/**
 * Mock class to test autoloader.
 * @package EmailLog
 */
class MockEmailLogAutoloaderClass extends EmailLogAutoloader {
	protected $files = array();

	public function set_files( array $files ) {
		$this->files = $files;
	}

	protected function require_file( $file ) {
		return in_array( $file, $this->files );
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

		$this->loader->set_files( array(
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
}
