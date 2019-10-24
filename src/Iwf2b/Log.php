<?php

namespace Iwf2b;

use Psr\Log\LoggerInterface;

/**
 * Class Log
 * @package Iwf2b
 */
class Log implements LoggerInterface {
	protected $loggers = [];

	public static function get_instance() {
		static $instance;

		if ( ! $instance ) {
			$instance = new static();
		}

		return $instance;
	}

	public static function set_logger( LoggerInterface $logger, $key = 'default', array $levels = [] ) {
		static::get_instance()->loggers[ $key ] = compact( 'logger', 'levels' );
	}

	public static function __callStatic( $name, $arguments ) {
		return call_user_func_array( [ static::get_instance(), $name ], $arguments );
	}

	protected function __construct() {
	}

	public function emergency( $message, array $context = array() ) {
		$this->write_log( __FUNCTION__, $message, $context );
	}

	public function alert( $message, array $context = array() ) {
		$this->write_log( __FUNCTION__, $message, $context );
	}

	public function critical( $message, array $context = array() ) {
		$this->write_log( __FUNCTION__, $message, $context );
	}

	public function error( $message, array $context = array() ) {
		$this->write_log( __FUNCTION__, $message, $context );
	}

	public function warning( $message, array $context = array() ) {
		$this->write_log( __FUNCTION__, $message, $context );
	}

	public function notice( $message, array $context = array() ) {
		$this->write_log( __FUNCTION__, $message, $context );
	}

	public function info( $message, array $context = array() ) {
		$this->write_log( __FUNCTION__, $message, $context );
	}

	public function debug( $message, array $context = array() ) {
		$this->write_log( __FUNCTION__, $message, $context );
	}

	public function log( $level, $message, array $context = array() ) {
		$this->write_log( $level, $message, $context );
	}

	protected function write_log( $level, $message, array $context = [] ) {
		$use_loggers = [];

		if ( isset( $context['logger'] ) ) {
			$use_loggers = (array) $context['logger'];
			unset( $context['logger'] );
		}

		foreach ( $this->loggers as $key => $config ) {
			$in_use      = empty( $use_loggers ) || in_array( $key, $use_loggers );
			$match_level = empty( $config['levels'] ) || in_array( $level, $config['levels'] );

			if ( $in_use && $match_level ) {
				$config['logger']->{$level}( $message, $context );
			}
		}
	}
}