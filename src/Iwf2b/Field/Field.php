<?php

namespace Iwf2b\Field;

use Iwf2b\Arr;
use Iwf2b\Field\Rule\RuleInterface;
use Iwf2b\Text;

/**
 * Class Field
 * @package Iwf2b\Field
 */
class Field implements FieldInterface {
	/**
	 * Name
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * Label
	 *
	 * @var string
	 */
	protected $label = '';

	/**
	 * Value
	 *
	 * @var mixed
	 */
	protected $value;

	/**
	 * Choices list
	 *
	 * @var array
	 */
	protected $choices = [];

	/**
	 * Validation rules
	 *
	 * @var RuleInterface[]
	 */
	protected $rules = [];

	/**
	 * Validation errors
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * {@inheritdoc}
	 */
	public function __construct( $name, $label ) {
		$this->name  = $name;
		$this->label = $label ?: $name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_value( $escape = false ) {
		$value = $this->value;

		if ( $escape ) {
			Arr::apply( $value, function ( $value ) {
				return is_string( $value ) ? esc_html( $value ) : $value;
			} );
		}

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_choices() {
		return $this->choices;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * {@inheritdoc}
	 */
	public function add_rule( RuleInterface $rule ) {
		$this->rules[] = $rule;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_value( $value ) {
		$this->value = $value;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_choices( $choices ) {
		if ( is_array( $choices ) ) {
			$this->choices = $choices;

		} else if ( is_string( $choices ) ) {
			$this->choices = wp_parse_list( $choices );

		} else {
			throw new \InvalidArgumentException( "The \$choices must be an array, comma or space separated string." );
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_error( $message ) {
		$this->errors[] = $message;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function clear_rules() {
		foreach ( $this->rules as $i => $rule ) {
			unset( $this->rules[ $i ] );
		}

		$this->rules = [];

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate( $break_on_first_error = true ) {
		$this->errors = [];

		foreach ( $this->rules as $rule ) {
			$rule->set_value( $this->value );

			if ( $rule->validate() === false ) {
				$this->errors[] = $rule->get_message();

				if ( $break_on_first_error ) {
					break;
				}
			}
		}

		return $this->is_valid();
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_valid() {
		return count( $this->errors ) === 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __toString() {
		return Text::stringify( $this->get_value( true ) );
	}
}
