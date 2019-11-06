<?php

namespace Iwf2b\Field\Rule;

/**
 * Class TypeRule
 * @package Iwf2b\Field\Rule
 */
class TypeRule extends AbstractRule {
	/**
	 * Value type
	 *
	 * @var string
	 */
	public $type;

	/**
	 * {@inheritdoc}
	 */
	protected function do_validate() {
		if ( function_exists( 'is_' . $this->type ) && call_user_func( 'is_' . $this->type, $this->value ) ) {
			return true;
		}

		if ( function_exists( 'ctype_' . $this->type ) && call_user_func( 'ctype_' . $this->type, $this->value ) ) {
			return true;
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_param() {
		return 'type';
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_required_params() {
		return [ 'type' ];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get_default_message() {
		return __( 'This value should be of type %type%.', 'iwf2b' );
	}
}
