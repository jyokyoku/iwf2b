<?php

namespace Iwf2b\Field\Rule;

/**
 * Class LessThanRule
 * @package Iwf2b\Field\Rule
 */
class LessThanRule extends AbstractRule {
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
		return $this->comparison > $this->value;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_message() {
		return __( 'This value should be less than %comparison%.', 'iwf2b' );
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
