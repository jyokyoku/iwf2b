<?php

namespace Iwf2b\Field\Rule;

class ChoiceRule implements RuleInterface {
	use RuleTrait {
		RuleTrait::__construct as rule_construct;
	}

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
	 * Strict mode
	 *
	 * @var bool
	 */
	protected $strict = false;

	/**
	 * {@inheritdoc}
	 */
	public function __construct( $config = null ) {
		$this->rule_construct( $config );

		if ( is_string( $this->choices ) ) {
			$this->choices = wp_parse_list( $this->choices );
		}
	}

	/**
	 * {@inheritdoc}
	 */
	protected function do_validation() {
		if ( $this->multiple ) {
			if ( ! is_array( $this->value ) ) {
				throw new \UnexpectedValueException( 'The value must be an array.' );
			}

			foreach ( $this->value as $value ) {
				if ( ! in_array( $value, $this->choices, $this->strict ) ) {
					return false;
				}
			}

			return true;
		}

		return in_array( $this->value, $this->choices, $this->strict );
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