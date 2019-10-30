<?php

namespace Iwf2b\ThumbDriver;

/**
 * Interface ThumbDriverInterface
 * @package Iwf2b\ThumbDriver
 */
interface ThumbDriverInterface {
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