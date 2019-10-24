<?php

namespace Iwf2b;

use Iwf2b\ThumbDriver\ThumbDriverInterface;

/**
 * Class Thumb
 * @package Iwf2b
 */
class Thumb {
	/**
	 * Thumbnail driver
	 *
	 * @var ThumbDriverInterface
	 */
	protected $driver;

	/**
	 * Endpoint file
	 *
	 * @var string
	 */
	protected $endpoint = '';

	/**
	 * Thumb constructor.
	 */
	protected function __construct() {
	}

	/**
	 * @return Thumb
	 */
	public static function get_instance() {
		static $instance;

		if ( ! $instance ) {
			$instance = new static();
		}

		return $instance;
	}

	/**
	 * @param string $endpoint
	 */
	public static function set_driver( ThumbDriverInterface $driver, $endpoint ) {
		static::get_instance()->driver = $driver;

		if ( ! file_exists( $endpoint ) ) {
			throw new \InvalidArgumentException( sprintf( 'Thumbnail endpoint is not found. - %s', $endpoint ) );
		}

		if ( strpos( $endpoint, WP_CONTENT_DIR ) !== 0 ) {
			throw new \InvalidArgumentException( sprintf( 'Place the endpoint under the WP_CONTENT directory. - %s', $endpoint ) );
		}

		static::get_instance()->endpoint = str_replace( trailingslashit( WP_CONTENT_DIR ), '', $endpoint );
	}

	/**
	 * @param string $src
	 * @param int $width
	 * @param int $height
	 * @param array $args
	 *
	 * @return string
	 */
	public static function get_endpoint_url( $src, $width = null, $height = null, array $args = [] ) {
		$src_key = static::get_instance()->driver->get_source_key();

		if ( ! $src_key ) {
			throw new \UnexpectedValueException( sprintf( 'Returns empty value from %s::get_source_key()', get_class( static::get_instance()->driver ) ) );
		}

		$query     = [ $src_key => $src ];
		$width_key = static::get_instance()->driver->get_width_key();

		if ( $width && $width_key ) {
			$query[ $width_key ] = $width;
		}

		$height_key = static::get_instance()->driver->get_height_key();

		if ( $height && $height_key ) {
			$query[ $height_key ] = $height;
		}

		$query    = array_merge( $args, $query );
		$endpoint = static::get_instance()->endpoint;

		if ( $endpoint ) {
			$endpoint = trailingslashit( WP_CONTENT_URL ) . $endpoint;
		}

		return add_query_arg( $query, $endpoint );
	}
}