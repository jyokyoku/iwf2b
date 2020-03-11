<?php

namespace Iwf2b\Field\Rule;

/**
 * Class ExactLengthRule
 * @package Iwf2b\Field\Rule
 */
class ExactLengthRule  implements RuleInterface {
	use RuleTrait;

	/**
	 * Exact length
	 *
	 * @var int
	 */
	public $length;

	/**
	 * {@inheritdoc}
	 */
	protected function do_validation() {
		$length = mb_strlen( $this->value );

		return $this->length == $length;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_message() {
		return __( 'This value should have exactly %length% characters.', 'iwf2b' );
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
		return [ 'length' => 'int' ];
	}
}
