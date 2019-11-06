<?php

namespace Iwf2b\Field\Rule;

use Iwf2b\Field\Data\FileData;
use Iwf2b\Field\Data\UploadFileData;

/**
 * Class UploadFileRule
 * @package Iwf2b\Field\Rule
 */
class UploadFileRule extends AbstractRule {
	/**
	 * {@inheritdoc}
	 */
	protected function do_validate() {
		if ( $this->value instanceof UploadFileData ) {
			return $this->value->is_valid();
		}

		if ( $this->value instanceof FileData ) {
			return $this->value->exists();
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_message() {
		if ( $this->value instanceof UploadFileData ) {
			return $this->value->get_error_message();
		}

		return __( 'This value is not a valid uploaded file.', 'iwf2b' );
	}
}