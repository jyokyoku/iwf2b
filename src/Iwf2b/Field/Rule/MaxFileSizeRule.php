<?php

namespace Iwf2b\Field\Rule;

use Iwf2b\Field\Data\FileData;

/**
 * Class MaxFileSizeRule
 * @package Iwf2b\Field\Rule
 */
class MaxFileSizeRule extends AbstractRule {
	/**
	 * Max byte
	 *
	 * @var int
	 */
	protected $byte;

	/**
	 * {@inheritdoc}
	 */
	protected function do_validate() {
		if ( $this->value instanceof FileData ) {
			return $this->byte >= $this->value->get_size();
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_message() {
		return __( 'This file is too large. It should have %byte% bytes or less.', 'iwf2b' );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_param() {
		return 'byte';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_required_params() {
		return [ 'byte' ];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_param_types() {
		return [ 'byte' => 'int' ];
	}
}