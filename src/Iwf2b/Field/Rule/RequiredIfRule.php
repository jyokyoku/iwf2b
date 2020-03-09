<?php

namespace Iwf2b\Field\Rule;

use Iwf2b\Field\FieldInterface;

/**
 * Class RequiredRuleIf
 * @package Iwf2b\Field\Rule
 */
class RequiredIfRule extends AbstractRule {
	/**
	 * Comparison field instance
	 *
	 * @var FieldInterface
	 */
	public $field;

	/**
	 * Expected value
	 *
	 * @var mixed
	 */
	public $expected;

	/**
	 * {@inheritdoc}
	 */
	protected function do_validation() {
		$compare_value = $this->field->get_value();

		if ( $this->is_empty( $compare_value ) ) {
			return true;
		}

		if ( ! $this->is_empty( $this->expected ) && $this->expected != $compare_value ) {
			return true;
		}

		return ! $this->is_empty( $this->value );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function through_if_empty() {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_required_params() {
		return [ 'field' ];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_param_types() {
		return [ 'field' => 'Iwf2b\Field\Field' ];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_message() {
		return __( 'This value is required.', 'iwf2b' );
	}
}
