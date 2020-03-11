<?php

namespace Iwf2b\Field\Rule;

use Iwf2b\Field\FieldInterface;
use Iwf2b\Field\FieldSet;

/**
 * Class RequiredRuleIf
 * @package Iwf2b\Field\Rule
 */
class RequiredIfRule extends AbstractRule {
	/**
	 * @var FieldSet
	 */
	protected $fieldset;

	/**
	 * @var FieldInterface|mixed
	 */
	protected $field;

	/**
	 * @var boolean
	 */
	protected $strict;

	/**
	 * Expected value
	 *
	 * @var mixed
	 */
	protected $expected;

	/**
	 * {@inheritdoc}
	 */
	public function __construct( $config = null ) {
		parent::__construct( $config );

		if ( ! $this->field instanceof FieldInterface && ! $this->fieldset ) {
			throw new \UnexpectedValueException( "The 'fieldset' property must be required if the 'field' property is not instance of Iwf2b\Field\FieldInterface." );
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function do_validation() {
		if ( $this->field instanceof FieldInterface ) {
			$compare_value = $this->field->get_value();

		} else if ( isset( $this->fieldset[ $this->field ] ) ) {
			$compare_value = $this->fieldset[ $this->field ]->get_value();

		} else {
			throw new \UnexpectedValueException( "The 'field' property must be the registered field or field name." );
		}

		if ( $this->is_empty( $compare_value ) ) {
			return true;
		}

		if ( ! $this->is_empty( $this->expected ) ) {
			if ( ( $this->strict && $this->expected !== $compare_value ) || ( ! $this->strict && $this->expected != $compare_value ) ) {
				return true;
			}
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
		return [
			'fieldset' => 'Iwf2b\Field\FieldSet',
			'field'    => [ 'string', 'Iwf2b\Field\FieldInterface' ],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_message() {
		return __( 'This value is required.', 'iwf2b' );
	}
}
