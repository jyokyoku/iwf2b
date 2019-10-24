<?php

namespace Iwf2b\Logger;

use Iwf2b\Text;
use Psr\Log\LoggerInterface;

class SimpleLogger implements LoggerInterface {
	protected $path = '';

	protected $file_format = '';

	protected $date_format = '';

	public function __construct( $path = null, array $args = [] ) {
		$args = shortcode_atts( [
			'file_format' => '%date%.log',
			'date_format' => 'Y-m-d H:i:s',
		], $args );

		if ( empty( $path ) ) {
			$path = WP_CONTENT_DIR . '/iwf2b-log/';
		}

		if ( ! is_dir( $path ) ) {
			if ( @mkdir( $path, 0777 ) && ! is_dir( $path ) ) {
				throw new \InvalidArgumentException( sprintf( 'Invalid log directory path. - %s', $this->path ) );
			}
		}

		if ( ! is_writable( $path ) ) {
			throw new \InvalidArgumentException( sprintf( 'The log directory is not writable. - %s', $this->path ) );
		}

		$this->path = $path;

		foreach ( $args as $arg_key => $arg_value ) {
			$this->{$arg_key} = $arg_value;
		}
	}

	public function emergency( $message, array $context = array() ) {
		$this->white_log( __FUNCTION__, $message, $context );
	}

	public function alert( $message, array $context = array() ) {
		$this->white_log( __FUNCTION__, $message, $context );
	}

	public function critical( $message, array $context = array() ) {
		$this->white_log( __FUNCTION__, $message, $context );
	}

	public function error( $message, array $context = array() ) {
		$this->white_log( __FUNCTION__, $message, $context );
	}

	public function warning( $message, array $context = array() ) {
		$this->white_log( __FUNCTION__, $message, $context );
	}

	public function notice( $message, array $context = array() ) {
		$this->white_log( __FUNCTION__, $message, $context );
	}

	public function info( $message, array $context = array() ) {
		$this->white_log( __FUNCTION__, $message, $context );
	}

	public function debug( $message, array $context = array() ) {
		$this->white_log( __FUNCTION__, $message, $context );
	}

	public function log( $level, $message, array $context = array() ) {
		$this->white_log( $level, $message, $context );
	}

	protected function white_log( $level, $message, array $context = array() ) {
		$message = sprintf( '%s [%s] %s', current_time( $this->date_format ), strtoupper( $level ), $this->format( $message ) ) . "\n";

		error_log( $message, 3, trailingslashit( $this->path ) . $this->get_file_name() );
	}

	protected function get_file_name() {
		$replaces = [
			'date' => current_time( 'Ymd' ),
		];

		return Text::replace( $this->file_format, $replaces );
	}

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