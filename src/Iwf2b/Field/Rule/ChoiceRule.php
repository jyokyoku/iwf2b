<?php

namespace Iwf2b\Field\Rule;

class ChoiceRule extends AbstractRule {
	/**
	 * Choices
	 *
	 * @var array
	 */
	protected $choices = [];

	/**
	 * Allow multiple choice
	 *
	 * @var bool
	 */
	protected $multiple = false;

	/**
	 * {@inheritdoc}
	 */
	public function __construct( $config = null ) {
		parent::__construct( $config );

		if ( is_string( $this->choices ) ) {
			$this->extension = wp_parse_list( $this->choices );
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function do_validate() {
		if ( $this->multiple ) {
			if ( ! is_array( $this->value ) ) {
				throw new \UnexpectedValueException( 'The value must be an array.' );
			}

			foreach ( $this->value as $value ) {
				if ( ! in_array( $value, $this->choices ) ) {
					return false;
				}
			}

			return true;
		}

		return in_array( $this->value, $this->choices );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_required_params() {
		return [ 'choices' ];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_param() {
		return 'choices';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_param_types() {
		return [ 'choices' => [ 'array', 'string' ] ];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_message() {
		return __( 'The value you selected is not a valid choice.', 'iwf2b' );
	}
}