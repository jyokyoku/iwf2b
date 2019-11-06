<?php

namespace Iwf2b\Field;

use Iwf2b\Arr;
use Iwf2b\Field\Data\FileData;
use Iwf2b\Field\Data\UploadFileData;

/**
 * Class Field
 * @package Iwf2b\Field
 */
class FieldUpload extends Field {
	/**
	 * {@inheritdoc}
	 */
	public function set_value( $value ) {
		if ( is_string( $value ) ) {
			$file = new FileData( $value );

			if ( $file->in_base_dir() ) {
				$value = $file;
			}
		}

		if ( is_array( $value ) ) {

			$args = Arr::merge_intersect_key( [
				'name'     => null,
				'tmp_name' => null,
				'error'    => null,
				'type'     => null,
				'size'     => null,
			], $value );

			$value = new UploadFileData( $args['tmp_name'], $args['name'], $args['type'], $args['error'] );
		}

		parent::set_value( $value );
	}

	/**
	 * {@inheritdoc}
	 */
	public function __toString() {
		if ( $this->get_value() instanceof FileData ) {
			return $this->get_value()->get_path();
		}

		return parent::__toString();
	}
}
