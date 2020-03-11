<?php

namespace Iwf2b\Field\Rule;

/**
 * Class RangeRule
 * @package Iwf2b\Field\Rule
 */
class RangeRule implements RuleInterface {
	use RuleTrait;

	/**
	 * Max value
	 *
	 * @var int|float
	 */
	public $max;

	/**
	 * Min value
	 *
	 * @var int|float
	 */
	public $min;

	/**
	 * {@inheritdoc}
	 */
	protected function do_validation() {
		if ( $this->max < $this->value ) {
			return false;
		}

		if ( $this->min > $this->value ) {
			return false;
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_message() {
		return __( 'This value should be between %min and %max%.', 'iwf2b' );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_required_params() {
		return [ 'max', 'min' ];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_param_types() {
		return [ 'max' => 'numeric', 'min' => 'numeric' ];
	}
}
