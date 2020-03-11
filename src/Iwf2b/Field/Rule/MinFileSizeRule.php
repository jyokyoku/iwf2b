<?php

namespace Iwf2b\Field\Rule;

use Iwf2b\Field\Data\FileData;

/**
 * Class MinFileSizeRule
 * @package Iwf2b\Field\Rule
 */
class MinFileSizeRule implements RuleInterface {
	use RuleTrait;

	/**
	 * Min byte
	 *
	 * @var int
	 */
	protected $byte;

	/**
	 * {@inheritdoc}
	 */
	protected function do_validation() {
		if ( $this->value instanceof FileData ) {
			return $this->byte <= $this->value->get_size();
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_message() {
		return __( 'This file is too small. It should have %byte% bytes or more.', 'iwf2b' );
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