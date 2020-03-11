<?php

namespace Iwf2b\Field\Rule;

use Iwf2b\Field\FieldInterface;
use Iwf2b\Field\FieldSet;

class RetypeRule extends AbstractRule {
	/**
	 * @var FieldSet
	 */
	protected $fieldset;

	/**
	 * @var FieldInterface|mixed
	 */
	protected $field;

	/**
	 * @var bool
	 */
	protected $strict = false;

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
			$expected_value = $this->field->get_value();

		} else if ( isset( $this->fieldset[ $this->field ] ) ) {
			$expected_value = $this->fieldset[ $this->field ]->get_value();

		} else {
			throw new \UnexpectedValueException( "The 'field' property must be the registered field or field name." );
		}

		return $this->strict ? $this->value === $expected_value : $this->value == $expected_value;
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
		return __( 'This value does not match.', 'iwf2b' );
	}
}