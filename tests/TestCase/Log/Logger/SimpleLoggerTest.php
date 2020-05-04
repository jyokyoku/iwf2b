<?php

namespace Iwf2b\Tests\TestCase\Log\Logger;

use Iwf2b\Log\Logger\SimpleLogger;
use org\bovigo\vfs\vfsStream;

class SimpleLoggerTest extends \WP_UnitTestCase {
	public function setUp() {
		parent::setUp();

		$this->root = vfsStream::setup();
	}

	public function test_write_log_with_custom_date_format() {
		$log_dir  = $this->root->url() . '/log';
		$log_file = $log_dir . '/' . current_time( 'Ymd' ) . '.log';

		// test default date format
		$logger = new SimpleLogger( $log_dir );

		$logger->debug( 'test default date format' );
		$this->assertEquals( current_time( 'Y-m-d H:i:s' ) . ' [DEBUG] test default date format' . PHP_EOL, file_get_contents( $log_file ) );
		@unlink( $log_file );

		// test custom date format
		$logger = new SimpleLogger( $log_dir, [ 'date_format' => 'Y/m/d_H-i-s' ] );

		$logger->debug( 'test custom date format' );
		$this->assertEquals( current_time( 'Y/m/d_H-i-s' ) . ' [DEBUG] test custom date format' . PHP_EOL, file_get_contents( $log_file ) );
		@unlink( $log_file );
	}

	public function test_write_log_with_custom_file_name() {
		$log_dir          = $this->root->url() . '/log';
		$default_log_file = $log_dir . '/' . current_time( 'Ymd' ) . '.log';
		$debug_log_file   = $log_dir . '/debug_' . current_time( 'Ymd' ) . '.log';
		$alert_log_file   = $log_dir . '/alert_' . current_time( 'Ymd' ) . '.log';

		// test default file name
		$logger = new SimpleLogger( $log_dir );

		$logger->debug( 'test default file name' );
		$this->assertTrue( is_file( $default_log_file ) );
		$this->assertFalse( is_file( $alert_log_file ) );
		$this->assertFalse( is_file( $debug_log_file ) );
		@unlink( $default_log_file );

		// test custom log file name
		$logger = new SimpleLogger( $log_dir, [ 'file_name_format' => '%level%_%date%.log' ] );

		$logger->debug( 'test debug file name' );
		$this->assertTrue( is_file( $debug_log_file ) );
		$this->assertFalse( is_file( $alert_log_file ) );
		$this->assertFalse( is_file( $default_log_file ) );
		@unlink( $debug_log_file );

		$logger->alert( 'test alert file name' );
		$this->assertTrue( is_file( $alert_log_file ) );
		$this->assertFalse( is_file( $debug_log_file ) );
		$this->assertFalse( is_file( $default_log_file ) );
		@unlink( $alert_log_file );
	}

	public function test_write_log_with_split_dir() {
		$log_dir = $this->root->url() . '/log';
		$logger  = new SimpleLogger( $log_dir, [ 'split_dir' => true ] );

		$logger->debug( 'test debug log' );
		$debug_log_file = $log_dir . '/debug/' . current_time( 'Ymd' ) . '.log';
		$this->assertTrue( is_file( $debug_log_file ) );

		@unlink( $debug_log_file );

		$logger->alert( 'test alert log' );
		$alert_log_file = $log_dir . '/alert/' . current_time( 'Ymd' ) . '.log';
		$this->assertTrue( is_file( $alert_log_file ) );

		@unlink( $alert_log_file );
	}

	public function test_write_log_for_all_error_levels() {
		$log_dir = $this->root->url() . '/log';

		$logger = new SimpleLogger( $log_dir );

		$logger->debug( 'test debug log' );
		$log_file = $log_dir . '/' . current_time( 'Ymd' ) . '.log';
		$this->assertEquals( current_time( 'Y-m-d H:i:s' ) . ' [DEBUG] test debug log' . PHP_EOL, file_get_contents( $log_file ) );
		@unlink( $log_file );

		$logger->info( 'test info log' );
		$log_file = $log_dir . '/' . current_time( 'Ymd' ) . '.log';
		$this->assertEquals( current_time( 'Y-m-d H:i:s' ) . ' [INFO] test info log' . PHP_EOL, file_get_contents( $log_file ) );
		@unlink( $log_file );

		$logger->notice( 'test notice log' );
		$log_file = $log_dir . '/' . current_time( 'Ymd' ) . '.log';
		$this->assertEquals( current_time( 'Y-m-d H:i:s' ) . ' [NOTICE] test notice log' . PHP_EOL, file_get_contents( $log_file ) );
		@unlink( $log_file );

		$logger->warning( 'test warning log' );
		$log_file = $log_dir . '/' . current_time( 'Ymd' ) . '.log';
		$this->assertEquals( current_time( 'Y-m-d H:i:s' ) . ' [WARNING] test warning log' . PHP_EOL, file_get_contents( $log_file ) );
		@unlink( $log_file );

		$logger->error( 'test error log' );
		$log_file = $log_dir . '/' . current_time( 'Ymd' ) . '.log';
		$this->assertEquals( current_time( 'Y-m-d H:i:s' ) . ' [ERROR] test error log' . PHP_EOL, file_get_contents( $log_file ) );
		@unlink( $log_file );

		$logger->alert( 'test alert log' );
		$log_file = $log_dir . '/' . current_time( 'Ymd' ) . '.log';
		$this->assertEquals( current_time( 'Y-m-d H:i:s' ) . ' [ALERT] test alert log' . PHP_EOL, file_get_contents( $log_file ) );
		@unlink( $log_file );

		$logger->critical( 'test critical log' );
		$log_file = $log_dir . '/' . current_time( 'Ymd' ) . '.log';
		$this->assertEquals( current_time( 'Y-m-d H:i:s' ) . ' [CRITICAL] test critical log' . PHP_EOL, file_get_contents( $log_file ) );
		@unlink( $log_file );

		$logger->emergency( 'test emergency log' );
		$log_file = $log_dir . '/' . current_time( 'Ymd' ) . '.log';
		$this->assertEquals( current_time( 'Y-m-d H:i:s' ) . ' [EMERGENCY] test emergency log' . PHP_EOL, file_get_contents( $log_file ) );
		@unlink( $log_file );
	}

	public function test_write_log_with_context() {
		$log_dir = $this->root->url() . '/log';

		$logger = new SimpleLogger( $log_dir );

		$logger->debug( 'test debug log', [ 'var_name_1' => 'value_1', 'var_name_2' => 'value_2' ] );
		$log_file = $log_dir . '/' . current_time( 'Ymd' ) . '.log';
		$this->assertEquals( current_time( 'Y-m-d H:i:s' ) . ' [DEBUG] test debug log {"var_name_1":"value_1","var_name_2":"value_2"}' . PHP_EOL, file_get_contents( $log_file ) );
		@unlink( $log_file );
	}
}