<?php

namespace Iwf2b\ThumbDriver;

class TimthumbThumbDriver implements ThumbDriverInterface {
	public function get_source_key() {
		return 'src';
	}

	public function get_width_key() {
		return 'w';
	}

	public function get_height_key() {
		return 'h';
	}
}