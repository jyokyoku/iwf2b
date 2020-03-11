<?php

namespace Iwf2b\Field\Rule;

/**
 * Class GreaterThanOrEqualRule
 * @package Iwf2b\Field\Rule
 */
class GreaterThanOrEqualRule implements RuleInterface {
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
		return $this->comparison <= $this->value;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_message() {
		return __( 'This value should be greater than or equal to %comparison%.', 'iwf2b' );
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

	/**
	 * {@inheritdoc}
	 */
	protected function get_param_types() {
		return [ 'comparison' => 'numeric' ];
	}
}
