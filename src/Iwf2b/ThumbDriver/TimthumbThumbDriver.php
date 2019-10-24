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
	public function get_source_key() {
		return 'src';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_width_key() {
		return 'w';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_height_key() {
		return 'h';
	}
}