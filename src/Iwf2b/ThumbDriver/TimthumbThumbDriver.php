<?php

namespace Iwf2b\ThumbDriver;

/**
 * Class TimthumbThumbDriver
 * @package Iwf2b\ThumbDriver
 */
class TimthumbThumbDriver implements ThumbDriverInterface {
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