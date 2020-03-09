<?php

namespace Iwf2b\Field\Rule;

/**
 * Class MinLengthRule
 * @package Iwf2b\Field\Rule
 */
class MinLengthRule extends AbstractRule {
	/**
	 * Min length
	 *
	 * @var int
	 */
	public $length;

	/**
	 * {@inheritdoc}
	 */
	protected function do_validation() {
		$length = mb_strlen( $this->value );

		return $this->length <= $length;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_message() {
		return __( 'This value is too short. It should have %length% characters or more.', 'iwf2b' );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_param() {
		return 'length';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_required_params() {
		return [ 'length' ];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_param_types() {
		return [ 'comparison' => 'int' ];
	}
}
