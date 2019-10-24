<?php

namespace Iwf2b\ThumbDriver;

/**
 * Interface ThumbDriverInterface
 * @package Iwf2b\ThumbDriver
 */
interface ThumbDriverInterface {
	/**
	 * @return string
	 */
	public function get_source_key();

	/**
	 * @return string
	 */
	public function get_width_key();

	/**
	 * @return string
	 */
	public function get_height_key();
}