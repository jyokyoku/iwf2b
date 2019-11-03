<?php

namespace Iwf2b\Thumb\Driver;

/**
 * Class TimthumbDriver
 * @package Iwf2b\Thumb\Driver
 */
class TimthumbDriver implements DriverInterface {
	/**
	 * {@inheritdoc}
	 */
	public function get_url( $endpoint_url, $src, $width = null, $height = null, array $args = [] ) {
		$query        = $args;
		$query['src'] = $src;

		if ( $width ) {
			$query['w'] = (int) $width;
		}

		if ( $height ) {
			$query['h'] = (int) $height;
		}

		return add_query_arg( $query, $endpoint_url );
	}
}