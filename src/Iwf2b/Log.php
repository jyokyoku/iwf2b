<?php

namespace Iwf2b;

use Psr\Log\LoggerInterface;

/**
 * Class Log
 * @package Iwf2b
 */
class Log implements LoggerInterface {
	/**
	 * Logger instances
	 *
	 * @var array
	 */
	protected $loggers = [];

	/**
	 * @return Log
	 */
	public static function get_instance() {
		static $instance;

		if ( ! $instance ) {
			$instance = new static();
		}

		return $instance;
	}

	/**
	 * @param LoggerInterface $logger
	 * @param string $key
	 * @param array $levels
	 */
	public static function set_logger( LoggerInterface $logger, $key = 'default', array $levels = [] ) {
		static::get_instance()->loggers[ $key ] = compact( 'logger', 'levels' );
	}

	/**
	 * @param $name
	 * @param $arguments
	 *
	 * @return mixed
	 */
	public static function __callStatic( $name, $arguments ) {
		return call_user_func_array( [ static::get_instance(), $name ], $arguments );
	}

	/**
	 * Log constructor.
	 */
	protected function __construct() {
	}

	/**
	 * @param mixed $message
	 * @param array $context
	 */
	public function emergency( $message, array $context = array() ) {
		$this->write_log( __FUNCTION__, $message, $context );
	}

	/**
	 * @param mixed $message
	 * @param array $context
	 */
	public function alert( $message, array $context = array() ) {
		$this->write_log( __FUNCTION__, $message, $context );
	}

	/**
	 * @param mixed $message
	 * @param array $context
	 */
	public function critical( $message, array $context = array() ) {
		$this->write_log( __FUNCTION__, $message, $context );
	}

	/**
	 * @param mixed $message
	 * @param array $context
	 */
	public function error( $message, array $context = array() ) {
		$this->write_log( __FUNCTION__, $message, $context );
	}

	/**
	 * @param mixed $message
	 * @param array $context
	 */
	public function warning( $message, array $context = array() ) {
		$this->write_log( __FUNCTION__, $message, $context );
	}

	/**
	 * @param mixed $message
	 * @param array $context
	 */
	public function notice( $message, array $context = array() ) {
		$this->write_log( __FUNCTION__, $message, $context );
	}

	/**
	 * @param string $message
	 * @param array $context
	 */
	public function info( $message, array $context = array() ) {
		$this->write_log( __FUNCTION__, $message, $context );
	}

	/**
	 * @param mixed $message
	 * @param array $context
	 */
	public function debug( $message, array $context = array() ) {
		$this->write_log( __FUNCTION__, $message, $context );
	}

	/***
	 * @param mixed $level
	 * @param mixed $message
	 * @param array $context
	 */
	public function log( $level, $message, array $context = array() ) {
		$this->write_log( $level, $message, $context );
	}

	/**
	 * @param string $level
	 * @param mixed $message
	 * @param array $context
	 */
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