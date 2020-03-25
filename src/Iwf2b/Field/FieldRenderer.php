<?php

namespace Iwf2b\Field;

use Iwf2b\Arr;
use Iwf2b\Form\FormChoice;
use Iwf2b\Form\FormRendererInterface;
use Iwf2b\Form\Form;

/**
 * Class FieldRenderer
 * @package Iwf2b\Field
 */
class FieldRenderer {
	/**
	 * Error field class
	 *
	 * @var string
	 */
	protected $error_class = 'error';

	/**
	 * Html template of error element
	 *
	 * @var string
	 */
	protected $error_html = '<div class="error">%s</div>';

	/**
	 * Fieldset instance
	 *
	 * @var FieldSet
	 */
	protected $field_set;

	/**
	 * Current field instance
	 *
	 * @var FieldInterface
	 */
	protected $current_field;

	/**
	 * FieldRenderer constructor.
	 *
	 * @param FieldSet $field_set
	 */
	public function __construct( FieldSet $field_set ) {
		$this->field_set = $field_set;
	}

	/**
	 * @param string $class
	 */
	public function set_error_class( $class ) {
		$this->error_class = $class;

		return $this;
	}

	/**
	 * @param string $error_element_tmpl
	 */
	public function set_error_html( $error_element_tmpl ) {
		$this->error_html = $error_element_tmpl;

		return $this;
	}

	/**
	 * @param string $field_name
	 * @param array|bool $args
	 *
	 * @return string
	 */
	public function errors( $field_name, $args = [] ) {
		$field        = $this->get_field( $field_name );
		$field_errors = $field->get_errors();

		if ( is_bool( $args ) ) {
			$args = [ 'first_only' => $args ];
		}

		$args = wp_parse_args( $args, [
			'first_only' => false,
			'prefix'     => '',
			'suffix'     => '',
		] );

		if ( ! $field_errors ) {
			return '';
		}

		$errors = [];

		foreach ( $field_errors as $field_error ) {
			$field_error = $args['prefix'] . $field_error . $args['suffix'];
			$errors[]    = $this->error_html ? sprintf( $this->error_html, $field_error ) : $field_error;

			if ( $args['first_only'] ) {
				break;
			}
		}

		return implode( "\n", $errors );
	}

	/**
	 * @param array|bool $args
	 *
	 * @return string
	 */
	public function all_errors( $args = [] ) {
		$errors = [];

		if ( is_bool( $args ) ) {
			$args = [ 'first_only' => $args ];
		}

		$args = wp_parse_args( $args, [
			'first_only' => false,
			'prefix'     => '',
			'suffix'     => '',
		] );

		foreach ( $this->field_set as $field ) {
			$field_errors = $field->get_errors();

			if ( ! $field_errors ) {
				continue;
			}

			foreach ( $field_errors as $field_error ) {
				$field_error = $args['prefix'] . $field_error . $args['suffix'];
				$errors[]    = $this->error_html ? sprintf( $this->error_html, $field_error ) : $field_error;

				if ( $args['first_only'] ) {
					break;
				}
			}
		}

		if ( ! $errors ) {
			return '';
		}

		return implode( "\n", $errors );
	}

	/**
	 * @return string
	 */
	public function hidden_fields() {
		$set_recursive = function ( &$hidden_fields, $field_name, $value, $depth = 0 ) use ( &$set_recursive ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $key => $_value ) {
					$set_recursive( $hidden_fields, $field_name . '[]', $_value, $depth + 1 );
				}

			} else {
				$hidden_fields[] = Form::hidden( $field_name )->set_value( $value );
			}
		};

		$hidden_fields = [];

		foreach ( $this->field_set as $field ) {
			$set_recursive( $hidden_fields, $field->get_name(), $field->get_value( true ) );
		}

		return implode( "\n", $hidden_fields );
	}

	/**
	 * @param string $field_name
	 * @param string $type
	 * @param array $attr
	 * @param array $args
	 *
	 * @return FormRendererInterface
	 */
	public function render( $field_name, $type, array $attr = [], array $args = [] ) {
		$args = Arr::merge_intersect_key( [
			'escape' => false,
		], $args );

		$field = $this->get_field( $field_name );

		if ( ! $field->is_valid() && $this->error_class ) {
			if ( isset( $attr['class'] ) && is_array( $attr['class'] ) ) {
				$attr['class'][] = $this->error_class;

			} else {
				$attr['class'] = ! empty( $attr['class'] ) ? $attr['class'] . ' ' . $this->error_class : $this->error_class;
			}
		}

		if ( method_exists( 'Iwf2b\Form\Form', $type ) ) {
			return Form::$type( $field->get_name(), $attr )->set_value( $args['escape'] ? htmlspecialchars( $field->get_value() ) : $field->get_value() );
		}

		if ( method_exists( 'Iwf2b\Form\FormChoice', $type ) ) {
			return FormChoice::$type( $field->get_name(), $field->get_choices(), $attr )->set_value( $args['escape'] ? htmlspecialchars( $field->get_value() ) : $field->get_value() );
		}

		throw new \InvalidArgumentException( sprintf( 'The type "%s" is invalid.', $type ) );
	}

	/**
	 * @param string $field_name
	 * @param string $type
	 * @param array $attr
	 * @param array $args
	 *
	 * @return FormRendererInterface
	 */
	public function __invoke( $field_name, $type, array $attr = [], array $args = [] ) {
		return $this->render( $field_name, $type, $attr, $args );
	}

	/**
	 * @param string $field_name
	 *
	 * @return FieldInterface
	 */
	protected function get_field( $field_name = null ) {
		if ( $field_name ) {
			if ( ! isset( $this->field_set[ $field_name ] ) ) {
				throw new \InvalidArgumentException( sprintf( 'The field "%s" is not registered in fieldset.', $field_name ) );
			}

			$this->current_field = $this->field_set[ $field_name ];

		} else {
			if ( ! $this->current_field ) {
				$this->current_field = reset( $this->field_set );

				if ( ! $this->current_field ) {
					throw new \LogicException( 'The fieldset is empty.' );
				}
			}
		}

		return $this->current_field;
	}
}
