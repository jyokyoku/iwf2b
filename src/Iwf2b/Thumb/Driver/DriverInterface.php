<?php

namespace Iwf2b\Thumb\Driver;

/**
 * Interface DriverInterface
 * @package Iwf2b\Thumb\Driver
 */
interface DriverInterface {
	/**
	 * @param string $endpoint_url
	 * @param string $src
	 * @param int $width
	 * @param int $height
	 * @param array $args
	 *
	 * @return string
	 */
	public function get_url( $endpoint_url, $src, $width = null, $height = null, array $args = [] );
}