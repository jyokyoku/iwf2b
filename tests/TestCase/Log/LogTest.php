<?php

namespace Iwf2b\Tests\TestCase\Log;

use Iwf2b\Log\Log;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class LogTest extends \WP_UnitTestCase {
	/**
	 * @var vfsStreamDirectory
	 */
	protected $root;

	public function setUp() {
		parent::setUp();

		$this->root = vfsStream::setup();
	}

	public function tearDown() {
		parent::tearDown();

		$log = Log::get_instance();
		$ref = new \ReflectionClass( Log::class );

		$prop = $ref->getProperty( 'loggers' );
		$prop->setAccessible( true );
		$prop->setValue( $log, [] );

		$prop = $ref->getProperty( 'scope' );
		$prop->setAccessible( true );
		$prop->setValue( $log, '' );

		$prop = $ref->getProperty( 'scope_once' );
		$prop->setAccessible( true );
		$prop->setValue( $log, '' );
	}

	public function test_get_instance() {
		$log1 = Log::get_instance();

		$this->assertInstanceOf( Log::class, $log1 );

		$log2 = Log::get_instance();

		$this->assertEquals( $log1, $log2 );
	}

	public function test_set_logger() {
		$logger1 = new TestLogger1();
		$logger2 = new TestLogger2();

		Log::set_logger( $logger1, 'test1' );
		Log::set_logger( $logger2, 'test2', [ 'levels' => [ 'warning', 'error' ] ] );

		$ref  = new \ReflectionClass( Log::class );
		$prop = $ref->getProperty( 'loggers' );
		$prop->setAccessible( true );

		$this->assertEquals( [
			'test1' => [
				'logger' => $logger1,
				'scopes' => [],
				'levels' => [],
			],
			'test2' => [
				'logger' => $logger2,
				'scopes' => [],
				'levels' => [ 'warning', 'error' ],
			],
		], $prop->getValue( Log::get_instance() ) );
	}

	public function test_channel() {
		$logger1 = new TestLogger1();
		$logger2 = new TestLogger2();

		Log::set_logger( $logger1, 'test1' );
		Log::set_logger( $logger2, 'test2' );

		$this->assertEquals( $logger1, Log::channel( 'test1' ) );
		$this->assertEquals( $logger2, Log::channel( 'test2' ) );

		$this->expectException( \OutOfBoundsException::class );
		$this->expectExceptionMessage( 'Unregistered channel name: invalid_logger' );

		Log::channel( 'invalid_logger' );
	}

	public function test_write_log() {
		$logger = new TestWritableLogger( $this->root );

		Log::set_logger( $logger, 'writable' );

		Log::debug( 'test debug log' );

		$this->assertTrue( file_exists( $this->root->url() . '/debug.log' ) );
		$this->assertEquals( '[debug] test debug log', file_get_contents( $this->root->url() . '/debug.log' ) );

		Log::info( 'test info log' );

		$this->assertTrue( file_exists( $this->root->url() . '/info.log' ) );
		$this->assertEquals( '[info] test info log', file_get_contents( $this->root->url() . '/info.log' ) );

		Log::notice( 'test notice log' );

		$this->assertTrue( file_exists( $this->root->url() . '/notice.log' ) );
		$this->assertEquals( '[notice] test notice log', file_get_contents( $this->root->url() . '/notice.log' ) );

		Log::warning( 'test warning log' );

		$this->assertTrue( file_exists( $this->root->url() . '/warning.log' ) );
		$this->assertEquals( '[warning] test warning log', file_get_contents( $this->root->url() . '/warning.log' ) );

		Log::error( 'test error log' );

		$this->assertTrue( file_exists( $this->root->url() . '/error.log' ) );
		$this->assertEquals( '[error] test error log', file_get_contents( $this->root->url() . '/error.log' ) );

		Log::critical( 'test critical log' );

		$this->assertTrue( file_exists( $this->root->url() . '/critical.log' ) );
		$this->assertEquals( '[critical] test critical log', file_get_contents( $this->root->url() . '/critical.log' ) );

		Log::alert( 'test alert log' );

		$this->assertTrue( file_exists( $this->root->url() . '/alert.log' ) );
		$this->assertEquals( '[alert] test alert log', file_get_contents( $this->root->url() . '/alert.log' ) );

		Log::emergency( 'test emergency log' );

		$this->assertTrue( file_exists( $this->root->url() . '/emergency.log' ) );
		$this->assertEquals( '[emergency] test emergency log', file_get_contents( $this->root->url() . '/emergency.log' ) );
	}

	public function test_scope() {
		$logger1 = new TestWritableLogger( $this->root, 'scope1' );
		$logger2 = new TestWritableLogger( $this->root, 'scope2' );

		Log::set_logger( $logger1, 'channel1', [ 'scopes' => 'scope1' ] );
		Log::set_logger( $logger2, 'channel2', [ 'scopes' => 'scope2' ] );

		// test for scope 1
		Log::scope( 'scope1' );
		Log::info( 'test info log to scope1 (1)' );

		$this->assertTrue( file_exists( $this->root->url() . '/scope1_info.log' ) );
		$this->assertFalse( file_exists( $this->root->url() . '/scope2_info.log' ) );
		$this->assertEquals( '[info] test info log to scope1 (1)', file_get_contents( $this->root->url() . '/scope1_info.log' ) );

		@unlink( $this->root->url() . '/scope1_info.log' );

		// test for scope 1 again
		Log::info( 'test info log to scope1 (2)' );

		$this->assertTrue( file_exists( $this->root->url() . '/scope1_info.log' ) );
		$this->assertFalse( file_exists( $this->root->url() . '/scope2_info.log' ) );
		$this->assertEquals( '[info] test info log to scope1 (2)', file_get_contents( $this->root->url() . '/scope1_info.log' ) );

		@unlink( $this->root->url() . '/scope1_info.log' );

		// test for scope 2
		Log::scope( 'scope2' );
		Log::info( 'test info log to scope2 (1)' );

		$this->assertFalse( file_exists( $this->root->url() . '/scope1_info.log' ) );
		$this->assertTrue( file_exists( $this->root->url() . '/scope2_info.log' ) );
		$this->assertEquals( '[info] test info log to scope2 (1)', file_get_contents( $this->root->url() . '/scope2_info.log' ) );

		@unlink( $this->root->url() . '/scope2_info.log' );
	}

	public function test_scope_once() {
		$logger1 = new TestWritableLogger( $this->root, 'scope1' );
		$logger2 = new TestWritableLogger( $this->root, 'scope2' );

		Log::set_logger( $logger1, 'channel1', [ 'scopes' => 'scope1' ] );
		Log::set_logger( $logger2, 'channel2', [ 'scopes' => 'scope2' ] );

		// test for scope 1
		Log::scope_once( 'scope1' );
		Log::info( 'test info log to scope1' );

		$this->assertTrue( file_exists( $this->root->url() . '/scope1_info.log' ) );
		$this->assertFalse( file_exists( $this->root->url() . '/scope2_info.log' ) );
		$this->assertEquals( '[info] test info log to scope1', file_get_contents( $this->root->url() . '/scope1_info.log' ) );

		@unlink( $this->root->url() . '/scope1_info.log' );

		// test for all scopes (empty scope)
		Log::info( 'test info log to all scopes' );

		$this->assertTrue( file_exists( $this->root->url() . '/scope1_info.log' ) );
		$this->assertTrue( file_exists( $this->root->url() . '/scope2_info.log' ) );
		$this->assertEquals( '[info] test info log to all scopes', file_get_contents( $this->root->url() . '/scope1_info.log' ) );
		$this->assertEquals( '[info] test info log to all scopes', file_get_contents( $this->root->url() . '/scope2_info.log' ) );

		@unlink( $this->root->url() . '/scope1_info.log' );
		@unlink( $this->root->url() . '/scope2_info.log' );

		// test for scope 2
		Log::scope_once( 'scope2' );
		Log::info( 'test info log to scope2' );

		$this->assertFalse( file_exists( $this->root->url() . '/scope1_info.log' ) );
		$this->assertTrue( file_exists( $this->root->url() . '/scope2_info.log' ) );
		$this->assertEquals( '[info] test info log to scope2', file_get_contents( $this->root->url() . '/scope2_info.log' ) );

		@unlink( $this->root->url() . '/scope2_info.log' );
	}
}

class TestLogger1 implements LoggerInterface {
	use LoggerTrait;

	public function log( $level, $message, array $context = array() ) {
	}
}

class TestLogger2 implements LoggerInterface {
	use LoggerTrait;

	public function log( $level, $message, array $context = array() ) {
	}
}

class TestWritableLogger implements LoggerInterface {
	use LoggerTrait;

	/**
	 * @var vfsStreamDirectory
	 */
	protected $log_dir;

	protected $log_file_prefix = '';

	public function __construct( $log_dir, $log_file_prefix = '' ) {
		$this->log_dir         = $log_dir;
		$this->log_file_prefix = $log_file_prefix;
	}

	public function log( $level, $message, array $context = array() ) {
		$message = sprintf( '[%s] %s', $level, $message );
		$prefix  = $this->log_file_prefix ? $this->log_file_prefix . '_' : '';

		vfsStream::newFile( $prefix . $level . '.log' )->at( $this->log_dir )->setContent( $message );
	}
}