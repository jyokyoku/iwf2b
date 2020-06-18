<?php

namespace Iwf2b\Log\Logger;

use Iwf2b\Arr;
use Iwf2b\Filesystem;
use Iwf2b\Text;
use Psr\Log\LoggerInterface;

/**
 * Class SimpleLogger
 * @package Iwf2b\Logger
 */
class SimpleLogger implements LoggerInterface {
	/**
	 * Log file path
	 *
	 * @var string
	 */
	protected $dir = '';

	/**
	 * Log file name template
	 *
	 * @var string
	 */
	protected $file_name_format = '';

	/**
	 * Log date format
	 *
	 * @var string
	 */
	protected $date_format = '';

	/**
	 * @var bool
	 */
	protected $split_dir = false;

	/**
	 * SimpleLogger constructor.
	 *
	 * @param string $dir
	 * @param array $args
	 */
	public function __construct( $dir = null, array $args = [] ) {
		$args = Arr::merge_intersect_key( [
			'file_name_format' => '%date%.log',
			'date_format'      => 'Y-m-d H:i:s',
			'split_dir'        => false,
		], $args );

		if ( empty( $dir ) ) {
			$dir = WP_CONTENT_DIR . '/iwf2b-log/';
		}

		Filesystem::mkdir( $dir );
		$this->dir = trailingslashit( $dir );

		foreach ( $args as $arg_key => $arg_value ) {
			$this->{$arg_key} = $arg_value;
		}
	}

	/**
	 * @param string $message
	 * @param array $context
	 */
	public function emergency( $message, array $context = [] ) {
		$this->write_log( __FUNCTION__, $message, $context );
	}

	/**
	 * @param string $message
	 * @param array $context
	 */
	public function alert( $message, array $context = [] ) {
		$this->write_log( __FUNCTION__, $message, $context );
	}

	/**
	 * @param string $message
	 * @param array $context
	 */
	public function critical( $message, array $context = [] ) {
		$this->write_log( __FUNCTION__, $message, $context );
	}

	/**
	 * @param string $message
	 * @param array $context
	 */
	public function error( $message, array $context = [] ) {
		$this->write_log( __FUNCTION__, $message, $context );
	}

	/**
	 * @param string $message
	 * @param array $context
	 */
	public function warning( $message, array $context = [] ) {
		$this->write_log( __FUNCTION__, $message, $context );
	}

	/**
	 * @param string $message
	 * @param array $context
	 */
	public function notice( $message, array $context = [] ) {
		$this->write_log( __FUNCTION__, $message, $context );
	}

	/**
	 * @param string $message
	 * @param array $context
	 */
	public function info( $message, array $context = [] ) {
		$this->write_log( __FUNCTION__, $message, $context );
	}

	/**
	 * @param string $message
	 * @param array $context
	 */
	public function debug( $message, array $context = [] ) {
		$this->write_log( __FUNCTION__, $message, $context );
	}

	/**
	 * @param mixed $level
	 * @param string $message
	 * @param array $context
	 */
	public function log( $level, $message, array $context = [] ) {
		$this->write_log( $level, $message, $context );
	}

	/**
	 * @param string $level
	 * @param string $message
	 * @param array $context
	 */
	protected function write_log( $level, $message, array $context = [] ) {
		$time    = current_time( $this->date_format );
		$message = $this->format( $message );

		if ( $context ) {
			$message = sprintf( '%s [%s] %s %s', $time, strtoupper( $level ), $message, json_encode( $context, JSON_UNESCAPED_UNICODE ) ) . "\n";

		} else {
			$message = sprintf( '%s [%s] %s', $time, strtoupper( $level ), $message ) . "\n";
		}

		if ( $this->split_dir ) {
			$dir = trailingslashit( $this->dir . $level );
			Filesystem::mkdir( $dir );

		} else {
			$dir = $this->dir;
		}

		$file_name = Text::replace( $this->file_name_format, [
			'date'  => current_time( 'Ymd' ),
			'level' => $level,
		] );

		error_log( $message, 3, $dir . $file_name );
	}

	/**
	 * @param mixed $message
	 *
	 * @return string
	 */
	protected function format( $message ) {
		if ( is_string( $message ) ) {
			return $message;
		}

		if ( is_array( $message ) ) {
			return var_export( $message, true );
		}

		if ( is_object( $message ) ) {
			if ( method_exists( $message, '__toString' ) ) {
				return (string) $message;
			}

			if ( $message instanceof \JsonSerializable ) {
				return json_encode( $message, JSON_UNESCAPED_UNICODE );
			}
		}

		return print_r( $message, true );
	}
}