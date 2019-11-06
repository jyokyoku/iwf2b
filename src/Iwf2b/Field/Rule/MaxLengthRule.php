<?php

namespace Iwf2b\Field\Rule;

/**
 * Class MaxLengthRule
 * @package Iwf2b\Field\Rule
 */
class MaxLengthRule extends AbstractRule {
	/**
	 * Max length
	 *
	 * @var int
	 */
	public $length;

	/**
	 * {@inheritdoc}
	 */
	protected function do_validate() {
		$length = mb_strlen( $this->value );

		return $this->length >= $length;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_message() {
		return __( 'This value is too long. It should have %length% characters or less.', 'iwf2b' );
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
