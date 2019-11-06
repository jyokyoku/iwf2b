<?php

namespace Iwf2b\Thumb;

use Iwf2b\Filesystem;
use Iwf2b\Thumb\Driver\DriverInterface;

/**
 * Class Thumb
 * @package Iwf2b
 */
class Thumb {
	/**
	 * Thumbnail driver
	 *
	 * @var DriverInterface
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
	final private function __construct() {
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
	public static function set_driver( DriverInterface $driver, $endpoint ) {
		static::get_instance()->driver = $driver;

		if ( ! file_exists( $endpoint ) ) {
			throw new \InvalidArgumentException( sprintf( 'Thumbnail endpoint is not found. - %s', $endpoint ) );
		}

		if ( ! Filesystem::is_child_path( $endpoint, WP_CONTENT_DIR ) ) {
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
	public static function url( $src, $width = null, $height = null, array $args = [] ) {
		$endpoint = trailingslashit( WP_CONTENT_URL ) . static::get_instance()->endpoint;

		return static::get_instance()->driver->get_url( $endpoint, $src, $width, $height, $args );
	}
}