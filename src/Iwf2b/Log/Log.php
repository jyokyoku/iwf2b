<?php

namespace Iwf2b\Log;

use Iwf2b\Arr;
use Psr\Log\LoggerInterface;

/**
 * Class Log
 * @package Iwf2b
 *
 * @method static debug( mixed $message, array $context = [] )
 * @method static info( mixed $message, array $context = [] )
 * @method static notice( mixed $message, array $context = [] )
 * @method static warning( mixed $message, array $context = [] )
 * @method static error( mixed $message, array $context = [] )
 * @method static critical( mixed $message, array $context = [] )
 * @method static alert( mixed $message, array $context = [] )
 * @method static emergency( mixed $message, array $context = [] )
 */
class Log {
	/**
	 * Logger instances
	 *
	 * @var array
	 */
	protected $loggers = [];

	/**
	 * Log scope
	 *
	 * @var string
	 */
	protected $scope;

	/**
	 * Log scope once
	 *
	 * @var string
	 */
	protected $scope_once;

	/**
	 * @var Log
	 */
	private static $instance;

	/**
	 * @return Log
	 */
	public static function get_instance() {
		if ( ! static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Log constructor.
	 */
	protected function __construct() {
	}

	/**
	 * @param string $name
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	public static function __callStatic( $name, array $arguments ) {
		array_unshift( $arguments, $name );

		return call_user_func_array( [ static::get_instance(), 'write_log' ], $arguments );
	}

	/**
	 * Set the logger
	 *
	 * @param LoggerInterface $logger
	 * @param string $channel
	 * @param array $levels
	 *
	 * @return Log
	 */
	public static function set_logger( LoggerInterface $logger, $channel, array $args = [] ) {
		$instance = static::get_instance();

		$args = Arr::merge_intersect_key( [
			'levels' => [],
			'scopes' => [],
		], $args );

		$instance->loggers[ $channel ] = [
			'logger' => $logger,
			'levels' => (array) $args['levels'],
			'scopes' => (array) $args['scopes'],
		];

		return $instance;
	}

	/**
	 * @param string|bool|null $scope
	 *
	 * @return Log
	 */
	public static function scope( $scope ) {
		$instance = static::get_instance();

		$instance->scope = $scope;

		return $instance;
	}

	/**
	 * @param string|bool|null $scope
	 *
	 * @return Log
	 */
	public static function scope_once( $scope ) {
		$instance = static::get_instance();

		$instance->scope_once = $scope;

		return $instance;
	}

	/**
	 * Get the logger
	 *
	 * @param string $channel
	 *
	 * @return LoggerInterface
	 */
	public static function channel( $channel ) {
		$instance = static::get_instance();

		if ( ! isset( $instance->loggers[ $channel ] ) ) {
			throw new \OutOfBoundsException( sprintf( 'Unregistered channel name: %s', $channel ) );
		}

		return $instance->loggers[ $channel ]['logger'];
	}

	/**
	 * @param string $level
	 * @param mixed $message
	 * @param array $context
	 */
	protected function write_log( $level, $message, array $context = [] ) {
		$scope = $this->scope_once ?: $this->scope;

		foreach ( $this->loggers as $name => $config ) {
			$match_scope = empty( $config['scopes'] ) || empty( $scope ) || in_array( $scope, $config['scopes'] );
			$match_level = empty( $config['levels'] ) || in_array( $level, $config['levels'] );

			if ( $match_scope && $match_level ) {
				$config['logger']->{$level}( $message, $context );
			}
		}

		if ( $this->scope_once ) {
			$this->scope_once = '';
		}
	}
}