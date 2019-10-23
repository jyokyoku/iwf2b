<?php

namespace Iwf2b;

use Psr\Log\LoggerInterface;

class Log {
	protected static $loggers = [];

	final public static function config( $key, LoggerInterface $logger, array $levels = [] ) {
		static::$loggers[ $key ] = [
			'instance' => $logger,
			'levels'   => $levels,
		];
	}

	public static function emergency( $message, array $context = array() ) {
		static::write_log( __FUNCTION__, $message, $context );
	}

	public static function alert( $message, array $context = array() ) {
		static::write_log( __FUNCTION__, $message, $context );
	}

	public static function critical( $message, array $context = array() ) {
		static::write_log( __FUNCTION__, $message, $context );
	}

	public static function error( $message, array $context = array() ) {
		static::write_log( __FUNCTION__, $message, $context );
	}

	public static function warning( $message, array $context = array() ) {
		static::write_log( __FUNCTION__, $message, $context );
	}

	public static function notice( $message, array $context = array() ) {
		static::write_log( __FUNCTION__, $message, $context );
	}

	public static function info( $message, array $context = array() ) {
		static::write_log( __FUNCTION__, $message, $context );
	}

	public static function debug( $message, array $context = array() ) {
		static::write_log( __FUNCTION__, $message, $context );
	}

	public static function write_log( $level, $message, array $context = [] ) {
		$use_loggers = [];

		if ( isset( $context['logger'] ) ) {
			$use_loggers = (array) $context['logger'];
			unset( $context['logger'] );
		}

		foreach ( static::$loggers as $key => $config ) {
			$in_scope    = empty( $use_loggers ) || in_array( $key, $use_loggers );
			$match_level = empty( $config['levels'] ) || in_array( $level, $config['levels'] );

			if ( $in_scope && $match_level ) {
				$config['instance']->{$level}( $message, $context );
			}
		}
	}
}