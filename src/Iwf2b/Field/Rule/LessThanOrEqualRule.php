<?php

namespace Iwf2b\Field\Rule;

/**
 * Class LessThanOrEqualRule
 * @package Iwf2b\Field\Rule
 */
class LessThanOrEqualRule implements RuleInterface {
	use RuleTrait;

	/**
	 * Comparison value
	 *
	 * @var int|float
	 */
	public $comparison;

	/**
	 * {@inheritdoc}
	 */
	protected function do_validation() {
		return $this->comparison >= $this->value;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_message() {
		return __( 'This value should be less than or equal to %comparison%.', 'iwf2b' );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_param() {
		return 'comparison';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_required_params() {
		return [ 'comparison' ];
	}
}
